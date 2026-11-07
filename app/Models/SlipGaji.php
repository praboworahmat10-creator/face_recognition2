<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class SlipGaji extends Model
{
    use HasFactory;

    protected $table = 'slip_gajis';

    protected $fillable = [
        'karyawan_id',
        'bulan',
        'tahun',
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_kehadiran',
        'tunjangan_transport_makan',
        'bonus',
        'potongan_bpjs',
        'potongan_terlambat',
        'potongan_lainnya',
        'total_hadir',
        'total_izin',
        'total_sakit',
        'total_cuti',
        'total_alpa',
        'total_pendapatan',
        'total_potongan',
        'gaji_bersih',
        'status',
        'catatan',
    ];

    protected $casts = [
        'bulan' => 'integer',
        'tahun' => 'integer',
        'gaji_pokok' => 'decimal:2',
        'tunjangan_jabatan' => 'decimal:2',
        'tunjangan_kehadiran' => 'decimal:2',
        'tunjangan_transport_makan' => 'decimal:2',
        'bonus' => 'decimal:2',
        'potongan_bpjs' => 'decimal:2',
        'potongan_terlambat' => 'decimal:2',
        'potongan_lainnya' => 'decimal:2',
        'total_hadir' => 'integer',
        'total_izin' => 'integer',
        'total_sakit' => 'integer',
        'total_cuti' => 'integer',
        'total_alpa' => 'integer',
        'total_pendapatan' => 'decimal:2',
        'total_potongan' => 'decimal:2',
        'gaji_bersih' => 'decimal:2',
    ];

    /**
     * Daftar komponen yang dijumlahkan menjadi total_pendapatan.
     */
    public const KOMPONEN_PENDAPATAN = [
        'gaji_pokok',
        'tunjangan_jabatan',
        'tunjangan_kehadiran',
        'tunjangan_transport_makan',
        'bonus',
    ];

    /**
     * Daftar komponen yang dijumlahkan menjadi total_potongan.
     */
    public const KOMPONEN_POTONGAN = [
        'potongan_bpjs',
        'potongan_terlambat',
        'potongan_lainnya',
    ];

    public const NAMA_BULAN = [
        1 => 'Januari',
        2 => 'Februari',
        3 => 'Maret',
        4 => 'April',
        5 => 'Mei',
        6 => 'Juni',
        7 => 'Juli',
        8 => 'Agustus',
        9 => 'September',
        10 => 'Oktober',
        11 => 'November',
        12 => 'Desember',
    ];

    // ─────────────────────────────────────────────
    // RELASI
    // ─────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    // ─────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────

    public function getNamaBulanAttribute(): string
    {
        return self::NAMA_BULAN[$this->bulan] ?? '—';
    }

    public function getPeriodeAttribute(): string
    {
        return $this->nama_bulan . ' ' . $this->tahun;
    }

    public function getNomorSlipAttribute(): string
    {
        return sprintf(
            'KPS/SG/%04d/%02d/%05d',
            $this->tahun,
            $this->bulan,
            $this->id ?? 0
        );
    }

    public function getGajiBersihRupiahAttribute(): string
    {
        return static::rupiah($this->gaji_bersih);
    }

    public function getStatusLabelAttribute(): string
    {
        return $this->status === 'published' ? 'Published' : 'Draft';
    }

    public function getStatusColorAttribute(): string
    {
        return $this->status === 'published' ? 'success' : 'warning';
    }

    public function getIsPublishedAttribute(): bool
    {
        return $this->status === 'published';
    }

    public function getTotalHariKerjaAttribute(): int
    {
        return $this->total_hadir
            + $this->total_izin
            + $this->total_sakit
            + $this->total_cuti
            + $this->total_alpa;
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopePublished($query)
    {
        return $query->where('status', 'published');
    }

    public function scopeDraft($query)
    {
        return $query->where('status', 'draft');
    }

    public function scopePeriode($query, ?int $bulan = null, ?int $tahun = null)
    {
        if ($bulan) {
            $query->where('bulan', $bulan);
        }
        if ($tahun) {
            $query->where('tahun', $tahun);
        }
        return $query;
    }

    public function scopeMilik($query, int $karyawanId)
    {
        return $query->where('karyawan_id', $karyawanId);
    }

    // ─────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────

    /**
     * Hitung ulang total pendapatan, potongan, dan gaji bersih
     * dari komponen-komponen yang tersimpan pada model.
     */
    public function hitungTotal(): static
    {
        $pendapatan = 0.0;
        foreach (self::KOMPONEN_PENDAPATAN as $komponen) {
            $pendapatan += (float) $this->{$komponen} ?? 0;
        }

        $potongan = 0.0;
        foreach (self::KOMPONEN_POTONGAN as $komponen) {
            $potongan += (float) $this->{$komponen} ?? 0;
        }

        $this->total_pendapatan = round($pendapatan, 2);
        $this->total_potongan = round($potongan, 2);
        $this->gaji_bersih = round($pendapatan - $potongan, 2);
        $this->gaji_total = $this->gaji_bersih;

        return $this;
    }

    public function publish(): bool
    {
        return $this->update(['status' => 'published']);
    }

    public function unpublish(): bool
    {
        return $this->update(['status' => 'draft']);
    }

    // ─────────────────────────────────────────────
    // STATIC HELPERS
    // ─────────────────────────────────────────────

    public static function rupiah($nilai, bool $prefix = true): string
    {
        $angka = number_format((float) $nilai, 0, ',', '.');
        return $prefix ? 'Rp ' . $angka : $angka;
    }

    /**
     * Ubah angka menjadi kalimat bahasa Indonesia (untuk baris "dengan huruf").
     */
    public static function angkaKeHuruf($nilai): string
    {
        $angka = (int) round(abs((float) $nilai));

        if ($angka === 0) {
            return 'nol';
        }

        $satuan = [
            '',
            'satu',
            'dua',
            'tiga',
            'empat',
            'lima',
            'enam',
            'tujuh',
            'delapan',
            'sembilan',
            'sepuluh',
            'sebelas',
        ];

        $terbilang = function (int $n) use (&$terbilang, $satuan): string {
            if ($n < 12) {
                return $satuan[$n];
            }
            if ($n < 20) {
                return $terbilang($n - 10) . ' belas';
            }
            if ($n < 100) {
                return $terbilang(intdiv($n, 10)) . ' puluh ' . $terbilang($n % 10);
            }
            if ($n < 200) {
                return 'seratus ' . $terbilang($n - 100);
            }
            if ($n < 1000) {
                return $terbilang(intdiv($n, 100)) . ' ratus ' . $terbilang($n % 100);
            }
            if ($n < 2000) {
                return 'seribu ' . $terbilang($n - 1000);
            }
            if ($n < 1000000) {
                return $terbilang(intdiv($n, 1000)) . ' ribu ' . $terbilang($n % 1000);
            }
            if ($n < 1000000000) {
                return $terbilang(intdiv($n, 1000000)) . ' juta ' . $terbilang($n % 1000000);
            }
            return $terbilang(intdiv($n, 1000000000)) . ' miliar ' . $terbilang($n % 1000000000);
        };

        $hasil = preg_replace('/\s+/', ' ', trim($terbilang($angka)));

        return ((float) $nilai < 0 ? 'minus ' : '') . $hasil;
    }

    /**
     * Rekap kehadiran karyawan pada bulan/tahun tertentu, diambil
     * langsung dari tabel presensis.
     *
     * @return array{total_hadir:int,total_izin:int,total_sakit:int,total_cuti:int,total_alpa:int,hari_kerja:int}
     */
    public static function rekapPresensi(int $karyawanId, int $bulan, int $tahun): array
    {
        $rows = Presensi::where('karyawan_id', $karyawanId)
            ->whereYear('created_at', $tahun)
            ->whereMonth('created_at', $bulan)
            ->get(['status', 'status_izin']);

        // Izin/sakit/cuti hanya dihitung bila sudah disetujui admin.
        $disetujui = fn($row) => $row->status_izin === 'approved';

        $hadir = $rows->where('status', 'hadir')->count();
        $izin = $rows->where('status', 'izin')->filter($disetujui)->count();
        $sakit = $rows->where('status', 'sakit')->filter($disetujui)->count();
        $cuti = $rows->where('status', 'cuti')->filter($disetujui)->count();
        $alpa = $rows->where('status', 'alpha')->count();

        return [
            'total_hadir' => $hadir,
            'total_izin' => $izin,
            'total_sakit' => $sakit,
            'total_cuti' => $cuti,
            'total_alpa' => $alpa,
            'hari_kerja' => static::hariKerja($bulan, $tahun),
        ];
    }

    /**
     * Jumlah hari kerja (Senin–Jumat) pada bulan/tahun tertentu.
     */
    public static function hariKerja(int $bulan, int $tahun): int
    {
        $mulai = Carbon::create($tahun, $bulan, 1)->startOfMonth();
        $selesai = $mulai->copy()->endOfMonth();
        $jumlah = 0;

        for ($hari = $mulai->copy(); $hari->lte($selesai); $hari->addDay()) {
            if (!$hari->isWeekend()) {
                $jumlah++;
            }
        }

        return $jumlah;
    }
}
