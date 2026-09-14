<?php

namespace App\Http\Controllers;

use App\Models\Presensi;
use App\Models\Karyawan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

class PresensiController extends Controller
{
    // ─────────────────────────────────────────────
    // HALAMAN UTAMA PRESENSI
    // ─────────────────────────────────────────────
    public function index(Request $request)
    {
        $karyawan = Karyawan::where('email', auth()->user()->email)->first();
        return view('presensi.index', compact('karyawan'));
    }

    // ─────────────────────────────────────────────
    // RIWAYAT PRESENSI KARYAWAN
    // ─────────────────────────────────────────────
    public function riwayat(Request $request)
    {
        $karyawan = Karyawan::where('email', auth()->user()->email)->first();

        // Tab: harian atau bulanan
        $tab = $request->get('tab', 'harian');

        // Filter bulan untuk bulanan
        $bulan = $request->get('bulan', Carbon::now()->format('Y-m'));
        [$tahun, $bln] = explode('-', $bulan);

        // Riwayat Harian — 30 hari terakhir
        $harianRaw = Presensi::where('karyawan_id', $karyawan?->id)
            ->orderBy('created_at', 'desc')
            ->take(100)
            ->get();

        // Riwayat Bulanan — grouped per bulan
        $bulananRaw = Presensi::where('karyawan_id', $karyawan?->id)
            ->whereYear('waktu_datang', $tahun) // Use waktu_datang to be accurate with cuti dates
            ->whereMonth('waktu_datang', $bln)
            ->orderBy('waktu_datang', 'asc')
            ->get();

        // Helper untuk proses tampilan
        $processRiwayat = function($collection) {
            $processed = collect();
            $seenCuti = [];
            foreach ($collection as $item) {
                if (in_array($item->status, ['cuti', 'izin', 'sakit']) && $item->cuti_group_id) {
                    if (in_array($item->cuti_group_id, $seenCuti)) continue;
                    $seenCuti[] = $item->cuti_group_id;
                    $start = \Carbon\Carbon::parse($item->cuti_start)->format('d M Y');
                    $end = \Carbon\Carbon::parse($item->cuti_end)->format('d M Y');
                    $item->tanggal_display = $start == $end ? $start : "$start s/d $end";
                } else {
                    $waktu = $item->waktu_datang ?? $item->created_at;
                    $item->tanggal_display = \Carbon\Carbon::parse($waktu)->format('d M Y');
                }
                $processed->push($item);
            }
            return $processed;
        };

        $harian = $processRiwayat($harianRaw);
        $bulanan = $processRiwayat($bulananRaw);

        // Summary bulanan (menghitung hari aktual)
        $summary = [
            'hadir'  => $bulananRaw->whereNotNull('waktu_datang')->where('status', 'hadir')->count(),
            'pulang' => $bulananRaw->whereNotNull('waktu_pulang')->where('status', 'hadir')->count(),
            'izin'   => $bulananRaw->where('status', 'izin')->where('status_izin', 'approved')->count(),
            'sakit'  => $bulananRaw->where('status', 'sakit')->where('status_izin', 'approved')->count(),
            'cuti'   => $bulananRaw->where('status', 'cuti')->where('status_izin', 'approved')->count(),
            'alpha'  => $bulananRaw->where('status', 'alpha')->count(),
        ];

        return view('presensi.riwayat', compact('karyawan', 'harian', 'bulanan', 'summary', 'tab', 'bulan'));
    }

    // ─────────────────────────────────────────────
    // SIMPAN PRESENSI
    // ─────────────────────────────────────────────
    public function simpan(Request $request)
    {
        $request->validate([
            'nik'            => 'required|string',
            'latitude'       => 'required|numeric',
            'longitude'      => 'required|numeric',
            'confidence'     => 'required|string',
            'timestamp'      => 'required|string',
            'jenis_presensi' => 'required|in:masuk,pulang',
        ]);

        $karyawan = Karyawan::where('nik', $request->input('nik'))->first();
        $karyawanLogin = Karyawan::where('email', Auth::user()->email)->first();

        if (!$karyawanLogin || $karyawanLogin->nik !== $karyawan->nik) {
            return response()->json(['success' => false, 'message' => 'Wajah tidak sesuai dengan akun yang login!'], 403);
        }

        if (!$karyawan) {
            return response()->json(['success' => false, 'message' => 'Karyawan tidak ditemukan.'], 404);
        }

        $kantorLat = config('kantor.latitude');
        $kantorLng = config('kantor.longitude');
        $maxRadius = config('kantor.radius_meter');

        $distance = $this->hitungJarak((float) $request->input('latitude'), (float) $request->input('longitude'), $kantorLat, $kantorLng);

        if ($distance > $maxRadius) {
            return response()->json(['success' => false, 'message' => "Anda berada {$distance}m dari lokasi. Batas maksimal {$maxRadius}m."], 422);
        }

        $confidenceStr   = $request->input('confidence');
        $confidenceScore = (float) str_replace('%', '', $confidenceStr) / 100;

        if ($confidenceScore < 0.50) {
            return response()->json(['success' => false, 'message' => 'Akurasi wajah terlalu rendah. Coba lagi dengan pencahayaan baik.'], 422);
        }

        $waktuAbsen = now();
        
        $buktiPath = null;
        if ($request->filled('snapshot')) {
            $base64 = $request->input('snapshot');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64)) {
                $data = substr($base64, strpos($base64, ',') + 1);
                $data = base64_decode($data);
                $fileName = 'bukti-presensi/' . $karyawan->nik . '_' . time() . '.jpg';
                Storage::disk('public')->put($fileName, $data);
                $buktiPath = $fileName;
            }
        }

        // Cek sudah presensi hari ini
        $presensiHariIni = Presensi::where('karyawan_id', $karyawan->id)
            ->whereDate('created_at', Carbon::today())
            ->first();

        $jenis = $request->input('jenis_presensi');
        
        if ($jenis === 'pulang') {
            if (!$presensiHariIni) {
                return response()->json(['success' => false, 'message' => 'Anda belum melakukan absen masuk hari ini.'], 422);
            }
            if ($presensiHariIni->waktu_pulang) {
                return response()->json(['success' => false, 'message' => 'Anda sudah melakukan absen pulang hari ini.'], 422);
            }
            
            // Update pulang
            $presensiHariIni->update([
                'waktu_pulang' => $waktuAbsen,
                'bukti_pulang' => $buktiPath
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Absen Pulang berhasil dicatat!',
                'data'    => [
                    'id'              => $presensiHariIni->id,
                    'nik'             => $karyawan->nik,
                    'nama'            => $karyawan->nama,
                    'waktu'           => $waktuAbsen->format('H:i:s'),
                    'status'          => 'pulang',
                ],
            ]);
        }

        // jenis === 'masuk'
        if ($presensiHariIni) {
            return response()->json(['success' => false, 'message' => 'Anda sudah melakukan absen masuk hari ini.'], 422);
        }

        // Simpan presensi datang
        $presensi = Presensi::create([
            'karyawan_id'     => $karyawan->id,
            'bukti'           => $buktiPath,
            'nik'             => $karyawan->nik,
            'nama'            => $karyawan->nama,
            'status'          => 'hadir',
            'latitude'        => $request->input('latitude'),
            'longitude'       => $request->input('longitude'),
            'distance_meter'  => $distance,
            'face_confidence' => $confidenceScore,
            'waktu_datang'    => $waktuAbsen,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Absen Masuk berhasil dicatat!',
            'data'    => [
                'id'              => $presensi->id,
                'nik'             => $karyawan->nik,
                'nama'            => $karyawan->nama,
                'waktu'           => $waktuAbsen->format('H:i:s'),
                'status'          => 'datang',
                'confidence'      => round($confidenceScore * 100, 1) . '%',
                'distance_meter'  => $distance,
            ],
        ]);
    }

    // ─────────────────────────────────────────────
    // API: FACE DESCRIPTORS
    // ─────────────────────────────────────────────
    public function getFaceDescriptors()
    {
        $karyawans = Karyawan::whereNotNull('face_descriptors')
            ->get(['nik', 'nama', 'face_descriptors'])
            ->makeVisible(['face_descriptors'])
            ->map(function ($k) {
                $raw = json_decode($k->face_descriptors, true);
                if (!$raw || !is_array($raw)) return null;
                return [
                    'nim'         => $k->nik, // UI JS still uses 'nim' payload mapping
                    'nik'         => $k->nik,
                    'nama'        => $k->nama,
                    'descriptors' => is_array($raw[0] ?? null) ? $raw : [$raw],
                ];
            })->filter()->values();

        return response()->json($karyawans);
    }

    // ─────────────────────────────────────────────
    // API: GET KARYAWAN BY NIK
    // ─────────────────────────────────────────────
    public function getMahasiswaByNim($nik)
    {
        $karyawan = Karyawan::where('nik', $nik)->first();

        if (!$karyawan) {
            return response()->json(['error' => 'Karyawan tidak ditemukan'], 404);
        }

        return response()->json([
            'nim'      => $karyawan->nik, // Fallback for UI JS
            'nik'      => $karyawan->nik,
            'nama'     => $karyawan->nama,
            'prodi'    => $karyawan->divisi, // UI JS maps
            'divisi'   => $karyawan->divisi,
            'fakultas' => $karyawan->jabatan, // UI JS maps
            'jabatan'  => $karyawan->jabatan,
            'semester' => '', // UI JS maps
            'angkatan' => '', // UI JS maps
            'foto_url' => $karyawan->foto ? Storage::url($karyawan->foto) : null,
        ]);
    }

    // ─────────────────────────────────────────────
    // VALIDASI LOKASI (AJAX)
    // ─────────────────────────────────────────────
    public function validasiLokasi(Request $request)
    {
        $request->validate([
            'latitude'  => 'required|numeric|between:-90,90',
            'longitude' => 'required|numeric|between:-180,180',
        ]);

        $kantorLat = config('kantor.latitude');
        $kantorLon = config('kantor.longitude');
        $radius    = config('kantor.radius_meter');

        $jarak = $this->hitungJarak((float) $request->latitude, (float) $request->longitude, $kantorLat, $kantorLon);
        $valid = $jarak <= $radius;

        return response()->json([
            'valid'       => $valid,
            'jarak_meter' => round($jarak, 2),
            'pesan'       => $valid ? 'Lokasi terverifikasi ✓' : "Jarak " . round($jarak) . "m. Batas {$radius}m.",
        ]);
    }

    // ─────────────────────────────────────────────
    // REKAP PRESENSI
    // ─────────────────────────────────────────────
    public function rekap(Request $request)
    {
        $query = Presensi::with('karyawan');

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->input('tanggal'));
        } else {
            $query->where(function ($q) {
                $q->whereNull('cuti_group_id')
                  ->orWhereRaw('DATE(waktu_datang) = cuti_start');
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('karyawan', function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
                })->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $presensis = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();

        return view('admin.rekap', compact('presensis'));
    }

    public function rekapBulanan(Request $request)
    {
        $bulan = $request->input('bulan', date('Y-m')); // Format: YYYY-MM
        
        $year = date('Y', strtotime($bulan));
        $month = date('m', strtotime($bulan));

        $query = Karyawan::where('status_karyawan', 'aktif');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $karyawans = $query->paginate(20)->withQueryString();

        foreach ($karyawans as $k) {
            $k->hadir_count = Presensi::where('nik', $k->nik)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'hadir')
                ->count();

            $k->izin_count = Presensi::where('nik', $k->nik)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'izin')
                ->where('status_izin', 'approved')
                ->count();

            $k->sakit_count = Presensi::where('nik', $k->nik)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'sakit')
                ->where('status_izin', 'approved')
                ->count();

            $k->alpha_count = Presensi::where('nik', $k->nik)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'alpha')
                ->count();

            $k->cuti_count = Presensi::where('nik', $k->nik)
                ->whereYear('created_at', $year)
                ->whereMonth('created_at', $month)
                ->where('status', 'cuti')
                ->where('status_izin', 'approved')
                ->count();

            $totalHariKerja = $k->hadir_count + $k->izin_count + $k->sakit_count + $k->cuti_count + $k->alpha_count;
            $k->rate = $totalHariKerja > 0 ? round(($k->hadir_count / $totalHariKerja) * 100, 1) : 0;
        }

        return view('admin.rekap-bulanan', compact('karyawans', 'bulan'));
    }

    public function exportBulanan(Request $request)
    {
        $bulan = $request->input('bulan', date('Y-m'));
        $year = date('Y', strtotime($bulan));
        $month = date('m', strtotime($bulan));

        $query = Karyawan::where('status_karyawan', 'aktif');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->where('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $karyawans = $query->get();

        $filename = 'rekap_bulanan_' . $bulan . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($karyawans, $year, $month) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NO', 'NIK', 'NAMA', 'DIVISI', 'JABATAN', 'HADIR', 'IZIN', 'SAKIT', 'CUTI', 'ALPHA', 'PERSENTASE KEHADIRAN']);

            foreach ($karyawans as $i => $k) {
                $hadir = Presensi::where('nik', $k->nik)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'hadir')
                    ->count();

                $izin = Presensi::where('nik', $k->nik)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'izin')
                    ->where('status_izin', 'approved')
                    ->count();

                $sakit = Presensi::where('nik', $k->nik)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'sakit')
                    ->where('status_izin', 'approved')
                    ->count();

                $cuti = Presensi::where('nik', $k->nik)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'cuti')
                    ->where('status_izin', 'approved')
                    ->count();

                $alpha = Presensi::where('nik', $k->nik)
                    ->whereYear('created_at', $year)
                    ->whereMonth('created_at', $month)
                    ->where('status', 'alpha')
                    ->count();

                $total = $hadir + $izin + $sakit + $cuti + $alpha;
                $rate = $total > 0 ? round(($hadir / $total) * 100, 1) . '%' : '0%';

                fputcsv($file, [
                    $i + 1,
                    $k->nik,
                    $k->nama,
                    $k->divisi,
                    $k->jabatan,
                    $hadir,
                    $izin,
                    $sakit,
                    $cuti,
                    $alpha,
                    $rate
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────
    // PETA LOKASI
    // ─────────────────────────────────────────────
    public function peta()
    {
        $presensis = Presensi::with('karyawan')
            ->whereDate('created_at', today())
            ->whereNotNull('latitude')
            ->get();
        return view('presensi.peta', compact('presensis'));
    }

    // ─────────────────────────────────────────────
    // EXPORT CSV
    // ─────────────────────────────────────────────
    public function export(Request $request)
    {
        $query = Presensi::with('karyawan');

        if ($request->filled('tanggal')) {
            $query->whereDate('created_at', $request->input('tanggal'));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function($q) use ($search) {
                $q->whereHas('karyawan', function($sq) use ($search) {
                    $sq->where('nama', 'like', "%{$search}%")
                       ->orWhere('nik', 'like', "%{$search}%");
                })->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('nik', 'like', "%{$search}%");
            });
        }

        $presensi = $query->orderBy('created_at', 'desc')->get();

        $tanggalSuffix = $request->input('tanggal') ?? 'semua_tanggal';
        $filename = 'rekap_presensi_' . $tanggalSuffix . '.csv';
        $headers  = [
            'Content-Type'        => 'text/csv',
            'Content-Disposition' => "attachment; filename={$filename}",
        ];

        $callback = function () use ($presensi) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['NO', 'NIK', 'NAMA', 'DIVISI', 'JABATAN', 'TANGGAL', 'STATUS', 'WAKTU DATANG', 'WAKTU PULANG', 'CONFIDENCE', 'JARAK (m)']);

            foreach ($presensi as $i => $p) {
                fputcsv($file, [
                    $i + 1,
                    $p->nik,
                    $p->nama,
                    $p->karyawan->divisi ?? '—',
                    $p->karyawan->jabatan ?? '—',
                    $p->tanggal,
                    strtoupper($p->status),
                    $p->waktu_datang ? $p->waktu_datang->format('H:i:s') : '—',
                    $p->waktu_pulang ? $p->waktu_pulang->format('H:i:s') : '—',
                    round(($p->face_confidence ?? 0) * 100, 1) . '%',
                    $p->distance_meter ?? '—',
                ]);
            }
            fclose($file);
        };
        return response()->stream($callback, 200, $headers);
    }

    // ─────────────────────────────────────────────
    // PENGAJUAN IZIN
    // ─────────────────────────────────────────────
    public function izin(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string',
            'bukti'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $karyawan = Karyawan::where('email', Auth::user()->email)->first();

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti-izin', 'public');
        }

        $start = \Carbon\Carbon::parse($request->tanggal_mulai);
        $end = \Carbon\Carbon::parse($request->tanggal_selesai);

        $dates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $cuti_group_id = 'izin_' . uniqid();
        foreach ($dates as $tgl) {
            Presensi::updateOrCreate(
                [
                    'karyawan_id'  => optional($karyawan)->id,
                    'waktu_datang' => $tgl . ' 08:00:00',
                ],
                [
                    'nik'           => optional($karyawan)->nik,
                    'nama'          => optional($karyawan)->nama,
                    'status'        => 'izin',
                    'keterangan'    => $request->keterangan,
                    'status_izin'   => 'pending',
                    'bukti'         => $buktiPath,
                    'created_at'    => $tgl . ' 08:00:00',
                    'cuti_group_id' => $cuti_group_id,
                    'cuti_start'    => $request->tanggal_mulai,
                    'cuti_end'      => $request->tanggal_selesai,
                ]
            );
        }

        return back()->with('success', 'Pengajuan izin berhasil dikirim.');
    }

    // ─────────────────────────────────────────────
    // PENGAJUAN SAKIT
    // ─────────────────────────────────────────────
    public function sakit(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string',
            'bukti'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $karyawan = Karyawan::where('email', Auth::user()->email)->first();

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti-sakit', 'public');
        }

        $start = \Carbon\Carbon::parse($request->tanggal_mulai);
        $end = \Carbon\Carbon::parse($request->tanggal_selesai);

        $dates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $cuti_group_id = 'sakit_' . uniqid();
        foreach ($dates as $tgl) {
            Presensi::updateOrCreate(
                [
                    'karyawan_id'  => optional($karyawan)->id,
                    'waktu_datang' => $tgl . ' 08:00:00',
                ],
                [
                    'nik'           => optional($karyawan)->nik,
                    'nama'          => optional($karyawan)->nama,
                    'status'        => 'sakit',
                    'keterangan'    => $request->keterangan,
                    'status_izin'   => 'pending',
                    'bukti'         => $buktiPath,
                    'created_at'    => $tgl . ' 08:00:00',
                    'cuti_group_id' => $cuti_group_id,
                    'cuti_start'    => $request->tanggal_mulai,
                    'cuti_end'      => $request->tanggal_selesai,
                ]
            );
        }

        return back()->with('success', 'Pengajuan sakit berhasil dikirim.');
    }

    // ─────────────────────────────────────────────
    // PENGAJUAN CUTI
    // ─────────────────────────────────────────────
    public function cuti(Request $request)
    {
        $request->validate([
            'tanggal_mulai'   => 'required|date',
            'tanggal_selesai' => 'required|date|after_or_equal:tanggal_mulai',
            'keterangan'      => 'required|string',
            'bukti'           => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $karyawan = Karyawan::where('email', Auth::user()->email)->first();

        $buktiPath = null;
        if ($request->hasFile('bukti')) {
            $buktiPath = $request->file('bukti')->store('bukti-cuti', 'public');
        }

        $start = \Carbon\Carbon::parse($request->tanggal_mulai);
        $end = \Carbon\Carbon::parse($request->tanggal_selesai);

        $dates = [];
        for ($date = $start->copy(); $date->lte($end); $date->addDay()) {
            $dates[] = $date->format('Y-m-d');
        }

        $cuti_group_id = 'cuti_' . uniqid();
        foreach ($dates as $tgl) {
            Presensi::updateOrCreate(
                [
                    'karyawan_id'  => optional($karyawan)->id,
                    'waktu_datang' => $tgl . ' 08:00:00',
                ],
                [
                    'nik'           => optional($karyawan)->nik,
                    'nama'          => optional($karyawan)->nama,
                    'status'        => 'cuti',
                    'keterangan'    => $request->keterangan,
                    'status_izin'   => 'pending',
                    'bukti'         => $buktiPath,
                    'created_at'    => $tgl . ' 08:00:00',
                    'cuti_group_id' => $cuti_group_id,
                    'cuti_start'    => $request->tanggal_mulai,
                    'cuti_end'      => $request->tanggal_selesai,
                ]
            );
        }

        return back()->with('success', 'Pengajuan cuti berhasil dikirim.');
    }

    private function hitungJarak(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $R    = 6371000;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2 + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;
        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return round($R * $c, 2);
    }
}