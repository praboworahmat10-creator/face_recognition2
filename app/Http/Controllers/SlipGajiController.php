<?php

namespace App\Http\Controllers;

use App\Models\Karyawan;
use App\Models\SlipGaji;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class SlipGajiController extends Controller
{
    // ─────────────────────────────────────────────
    // INDEX — Slip gaji milik sendiri (published)
    // ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $karyawan = $this->karyawanLogin();

        if (!$karyawan) {
            return view('karyawan.slip-gaji.index', [
                'karyawan' => null,
                'slips' => collect(),
                'summary' => $this->summaryKosong(),
                'daftarTahun' => collect(),
                'tahun' => null,
            ]);
        }

        $tahun = $request->filled('tahun') ? (int) $request->input('tahun') : null;

        $slips = SlipGaji::milik($karyawan->id)
            ->published()
            ->when($tahun, fn($q, $t) => $q->where('tahun', $t))
            ->orderByDesc('tahun')
            ->orderByDesc('bulan')
            ->get();

        $summary = [
            'jumlah_slip' => $slips->count(),
            'total_diterima' => (float) $slips->sum('gaji_bersih'),
            'rata_rata' => $slips->count() > 0
                ? (float) $slips->sum('gaji_bersih') / $slips->count()
                : 0.0,
            'terakhir' => $slips->first(),
        ];

        $daftarTahun = SlipGaji::milik($karyawan->id)
            ->published()
            ->distinct()
            ->orderByDesc('tahun')
            ->pluck('tahun');

        return view('karyawan.slip-gaji.index', compact(
            'karyawan',
            'slips',
            'summary',
            'daftarTahun',
            'tahun'
        ));
    }

    // ─────────────────────────────────────────────
    // SHOW — Preview slip gaji sendiri
    // ─────────────────────────────────────────────

    public function show(int $id)
    {
        $slipGaji = $this->slipMilikSendiri($id);

        return view('karyawan.slip-gaji.show', compact('slipGaji'));
    }

    // ─────────────────────────────────────────────
    // CETAK — Versi siap cetak / save as PDF
    // ─────────────────────────────────────────────

    public function cetak(int $id)
    {
        $slipGaji = $this->slipMilikSendiri($id);

        return view('admin.slip-gaji.cetak', compact('slipGaji'));
    }

    // ─────────────────────────────────────────────
    // PRIVATE
    // ─────────────────────────────────────────────

    private function karyawanLogin(): ?Karyawan
    {
        return Karyawan::where('email', Auth::user()->email ?? '')->first();
    }

    /**
     * Ambil slip gaji yang benar-benar milik karyawan yang login
     * dan sudah dipublikasikan. Selain itu dianggap tidak ada.
     */
    private function slipMilikSendiri(int $id): SlipGaji
    {
        $karyawan = $this->karyawanLogin();

        if (!$karyawan) {
            throw new NotFoundHttpException('Data karyawan tidak ditemukan.');
        }

        $slip = SlipGaji::with('karyawan')
            ->milik($karyawan->id)
            ->published()
            ->find($id);

        if (!$slip) {
            throw new NotFoundHttpException('Slip gaji tidak ditemukan.');
        }

        return $slip;
    }

    private function summaryKosong(): array
    {
        return [
            'jumlah_slip' => 0,
            'total_diterima' => 0.0,
            'rata_rata' => 0.0,
            'terakhir' => null,
        ];
    }
}
