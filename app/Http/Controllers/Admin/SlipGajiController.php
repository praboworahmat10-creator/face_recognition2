<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Karyawan;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class SlipGajiController extends Controller
{
    // ─────────────────────────────────────────────
    // INDEX — Daftar slip gaji + filter + summary
    // ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $bulan      = $request->filled('bulan') ? (int) $request->input('bulan') : null;
        $tahun      = $request->filled('tahun') ? (int) $request->input('tahun') : null;
        $karyawanId = $request->filled('karyawan_id') ? (int) $request->input('karyawan_id') : null;

        $filter = fn($query) => $query
            ->when($bulan,      fn($q, $b) => $q->where('bulan', $b))
            ->when($tahun,      fn($q, $t) => $q->where('tahun', $t))
            ->when($karyawanId, fn($q, $k) => $q->where('karyawan_id', $k));

        $slips = SlipGaji::with('karyawan')
            ->tap($filter)
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->orderBy('karyawan_id')
            ->paginate(20)
            ->withQueryString();

        // Summary dihitung dari seluruh hasil filter, bukan hanya halaman aktif.
        $ringkasan = SlipGaji::query()->tap($filter);

        $summary = [
            'jumlah_slip'      => (clone $ringkasan)->count(),
            'jumlah_draft'     => (clone $ringkasan)->draft()->count(),
            'jumlah_published' => (clone $ringkasan)->published()->count(),
            'total_pendapatan' => (float) (clone $ringkasan)->sum('total_pendapatan'),
            'total_potongan'   => (float) (clone $ringkasan)->sum('total_potongan'),
            'total_payout'     => (float) (clone $ringkasan)->sum('gaji_bersih'),
        ];

        $karyawans    = Karyawan::orderBy('nama')->get(['id', 'nik', 'nama']);
        $daftarTahun  = SlipGaji::distinct()->orderByDesc('tahun')->pluck('tahun');

        if ($daftarTahun->isEmpty()) {
            $daftarTahun = collect([(int) date('Y')]);
        }

        return view('admin.slip-gaji.index', compact(
            'slips', 'summary', 'karyawans', 'daftarTahun', 'bulan', 'tahun', 'karyawanId'
        ));
    }

    // ─────────────────────────────────────────────
    // CREATE — Form input slip gaji baru
    // ─────────────────────────────────────────────

    public function create(Request $request)
    {
        $karyawans = Karyawan::aktif()->orderBy('nama')->get(['id', 'nik', 'nama', 'divisi', 'jabatan']);

        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);

        return view('admin.slip-gaji.create', compact('karyawans', 'bulan', 'tahun'));
    }

    public function store(Request $request)
    {
        $validated = $this->validateSlip($request);

        $slip = new SlipGaji($validated);
        $slip->fill($this->rekapUntuk(
            (int) $validated['karyawan_id'],
            (int) $validated['bulan'],
            (int) $validated['tahun']
        ));
        $slip->hitungTotal()->save();

        return redirect()->route('admin.slip-gaji.show', $slip)
            ->with('success', 'Slip gaji ' . $slip->karyawan->nama . ' periode ' . $slip->periode . ' berhasil dibuat.');
    }

    // ─────────────────────────────────────────────
    // EDIT / UPDATE
    // ─────────────────────────────────────────────

    public function edit(SlipGaji $slipGaji)
    {
        $slipGaji->load('karyawan');
        $karyawans = Karyawan::orderBy('nama')->get(['id', 'nik', 'nama', 'divisi', 'jabatan']);

        return view('admin.slip-gaji.edit', compact('slipGaji', 'karyawans'));
    }

    public function update(Request $request, SlipGaji $slipGaji)
    {
        $validated = $this->validateSlip($request, $slipGaji);

        $slipGaji->fill($validated);
        $slipGaji->fill($this->rekapUntuk(
            (int) $validated['karyawan_id'],
            (int) $validated['bulan'],
            (int) $validated['tahun']
        ));
        $slipGaji->hitungTotal()->save();

        return redirect()->route('admin.slip-gaji.show', $slipGaji)
            ->with('success', 'Slip gaji periode ' . $slipGaji->periode . ' berhasil diperbarui.');
    }

    // ─────────────────────────────────────────────
    // SHOW — Preview slip gaji
    // ─────────────────────────────────────────────

    public function show(SlipGaji $slipGaji)
    {
        $slipGaji->load('karyawan');

        return view('admin.slip-gaji.show', compact('slipGaji'));
    }

    // ─────────────────────────────────────────────
    // CETAK — Versi siap cetak / save as PDF
    // ─────────────────────────────────────────────

    public function cetak(SlipGaji $slipGaji)
    {
        $slipGaji->load('karyawan');

        return view('admin.slip-gaji.cetak', compact('slipGaji'));
    }

    // ─────────────────────────────────────────────
    // PUBLISH / UNPUBLISH
    // ─────────────────────────────────────────────

    public function publish(SlipGaji $slipGaji)
    {
        $slipGaji->publish();

        return back()->with('success', 'Slip gaji periode ' . $slipGaji->periode . ' telah dipublikasikan ke karyawan.');
    }

    public function unpublish(SlipGaji $slipGaji)
    {
        $slipGaji->unpublish();

        return back()->with('success', 'Slip gaji periode ' . $slipGaji->periode . ' dikembalikan ke status draft.');
    }

    // ─────────────────────────────────────────────
    // DESTROY
    // ─────────────────────────────────────────────

    public function destroy(SlipGaji $slipGaji)
    {
        $periode = $slipGaji->periode;
        $slipGaji->delete();

        return redirect()->route('admin.slip-gaji.index')
            ->with('success', 'Slip gaji periode ' . $periode . ' berhasil dihapus.');
    }

    // ─────────────────────────────────────────────
    // HELPER — Auto-hitung rekap presensi (AJAX)
    // ─────────────────────────────────────────────

    /**
     * Dipakai form create/edit untuk mengisi otomatis rekap kehadiran
     * dan usulan nominal tunjangan/potongan berdasarkan presensi.
     */
    public function hitungPresensi(Request $request)
    {
        $validated = $request->validate([
            'karyawan_id' => 'required|exists:karyawans,id',
            'bulan'       => 'required|integer|between:1,12',
            'tahun'       => 'required|integer|min:2000|max:2100',
        ]);

        $karyawan = Karyawan::findOrFail($validated['karyawan_id']);
        $bulan    = (int) $validated['bulan'];
        $tahun    = (int) $validated['tahun'];

        $rekap = SlipGaji::rekapPresensi($karyawan->id, $bulan, $tahun);
        $usul  = $this->usulanNominal($rekap);

        $sudahAda = SlipGaji::where('karyawan_id', $karyawan->id)
            ->where('bulan', $bulan)
            ->where('tahun', $tahun)
            ->first();

        return response()->json([
            'success'  => true,
            'karyawan' => [
                'nik'     => $karyawan->nik,
                'nama'    => $karyawan->nama,
                'divisi'  => $karyawan->divisi,
                'jabatan' => $karyawan->jabatan,
            ],
            'rekap'       => $rekap,
            'usulan'      => $usul,
            'periode'     => (SlipGaji::NAMA_BULAN[$bulan] ?? '—') . ' ' . $tahun,
            'slip_ada'    => (bool) $sudahAda,
            'slip_ada_id' => $sudahAda?->id,
        ]);
    }

    // ─────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────

    private function validateSlip(Request $request, ?SlipGaji $slipGaji = null): array
    {
        $unik = Rule::unique('slip_gajis')->where(
            fn($query) => $query
                ->where('karyawan_id', $request->input('karyawan_id'))
                ->where('bulan', $request->input('bulan'))
                ->where('tahun', $request->input('tahun'))
        );

        if ($slipGaji) {
            $unik->ignore($slipGaji->id);
        }

        $nominal = 'nullable|numeric|min:0|max:999999999999';

        return $request->validate([
            'karyawan_id'               => 'required|exists:karyawans,id',
            'bulan'                     => ['required', 'integer', 'between:1,12'],
            'tahun'                     => ['required', 'integer', 'min:2000', 'max:2100', $unik],
            'gaji_pokok'                => $nominal,
            'tunjangan_jabatan'         => $nominal,
            'tunjangan_kehadiran'       => $nominal,
            'tunjangan_transport_makan' => $nominal,
            'bonus'                     => $nominal,
            'potongan_bpjs'             => $nominal,
            'potongan_terlambat'        => $nominal,
            'potongan_lainnya'          => $nominal,
            'status'                    => 'required|in:draft,published',
            'catatan'                   => 'nullable|string|max:1000',
        ], [
            'tahun.unique'       => 'Slip gaji karyawan ini untuk periode tersebut sudah ada.',
            'bulan.between'      => 'Bulan harus di antara 1 sampai 12.',
            'karyawan_id.exists' => 'Karyawan yang dipilih tidak ditemukan.',
        ]);
    }

    /**
     * Ambil rekap kehadiran dari tabel presensis untuk disimpan ke slip.
     */
    private function rekapUntuk(int $karyawanId, int $bulan, int $tahun): array
    {
        $rekap = SlipGaji::rekapPresensi($karyawanId, $bulan, $tahun);
        unset($rekap['hari_kerja']);

        return $rekap;
    }

    /**
     * Usulan nominal tunjangan kehadiran & potongan berdasarkan rekap presensi.
     * Nilai ini hanya saran untuk form — admin tetap bisa mengubahnya.
     */
    private function usulanNominal(array $rekap): array
    {
        $uangHadirPerHari    = (int) config('gaji.uang_kehadiran_per_hari', 25000);
        $transportPerHari    = (int) config('gaji.transport_makan_per_hari', 30000);
        $potonganAlpaPerHari = (int) config('gaji.potongan_alpa_per_hari', 100000);

        return [
            'tunjangan_kehadiran'       => $rekap['total_hadir'] * $uangHadirPerHari,
            'tunjangan_transport_makan' => $rekap['total_hadir'] * $transportPerHari,
            'potongan_lainnya'          => $rekap['total_alpa'] * $potonganAlpaPerHari,
        ];
    }
}
