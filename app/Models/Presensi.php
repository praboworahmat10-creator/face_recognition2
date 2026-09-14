<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Presensi extends Model
{
    use HasFactory;

    protected $table = 'presensis';

    protected $fillable = [
        'karyawan_id',
        'nik',
        'nama',
        'status',
        'keterangan',
        'bukti',
        'bukti_pulang',
        'waktu_datang',
        'waktu_pulang',
        'latitude',
        'longitude',
        'status_izin',
        'izin_approved_at',
        'izin_approved_by',
        'izin_reject_reason',
        'face_confidence',
        'distance_meter',
        'cuti_group_id',
        'cuti_start',
        'cuti_end',
    ];

    protected $casts = [
        'waktu_datang'     => 'datetime',
        'waktu_pulang'     => 'datetime',
        'izin_approved_at' => 'datetime',
        'latitude'         => 'float',
        'longitude'        => 'float',
        'distance_meter'   => 'float',
        'face_confidence'  => 'float',
        'cuti_start'       => 'date',
        'cuti_end'         => 'date',
    ];

    // ─────────────────────────────────────────────
    // RELASI
    // ─────────────────────────────────────────────

    public function karyawan()
    {
        return $this->belongsTo(Karyawan::class, 'karyawan_id');
    }

    public function izinApprovedBy()
    {
        return $this->belongsTo(User::class, 'izin_approved_by');
    }

    // ─────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────

    public function getWaktuDatangFormatAttribute(): string
    {
        if (in_array($this->status, ['izin', 'sakit', 'cuti', 'alpha'])) {
            return '—';
        }
        if (!$this->waktu_datang) return '—';
        return Carbon::parse($this->waktu_datang)->format('H:i:s');
    }

    public function getWaktuPulangFormatAttribute(): string
    {
        if (in_array($this->status, ['izin', 'sakit', 'cuti', 'alpha'])) {
            return '—';
        }
        if (!$this->waktu_pulang) return '—';
        return Carbon::parse($this->waktu_pulang)->format('H:i:s');
    }

    public function getTanggalAttribute(): string
    {
        if (!$this->waktu_datang && !$this->created_at) return '—';
        $waktu = $this->waktu_datang ?? $this->created_at;
        return Carbon::parse($waktu)->translatedFormat('d F Y');
    }

    public function getConfidencePctAttribute(): float
    {
        return round(($this->face_confidence ?? 0) * 100, 1);
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'hadir' => '✓ Hadir',
            'izin'  => '⚠ Izin',
            'sakit' => '🤒 Sakit',
            'alpha' => '✗ Alpha',
            'cuti'  => '📅 Cuti',
            default => '—',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'hadir' => 'success',
            'izin'  => 'warning',
            'sakit' => 'info',
            'alpha' => 'danger',
            'cuti'  => 'warning',
            default => 'secondary',
        };
    }

    public function getJarakLabelAttribute(): string
    {
        if (is_null($this->distance_meter)) return '—';
        if ($this->distance_meter < 1000) return round($this->distance_meter) . ' m';
        return round($this->distance_meter / 1000, 2) . ' km';
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────

    public function scopeHariIni($query)
    {
        return $query->whereDate('created_at', Carbon::today());
    }

    public function scopeBulanIni($query)
    {
        return $query->whereMonth('created_at', now()->month)
                     ->whereYear('created_at', now()->year);
    }

    public function scopeStatus($query, string $status)
    {
        return $query->where('status', $status);
    }

    public function scopeTanggal($query, string $dari, string $sampai)
    {
        return $query->whereBetween(
            \DB::raw('DATE(created_at)'),
            [$dari, $sampai]
        );
    }

    // ─────────────────────────────────────────────
    // STATIC METHODS — STATISTIK
    // ─────────────────────────────────────────────

    public static function statistikHariIni(): array
    {
        $totalKaryawan = Karyawan::aktif()->count();
        $presensiHariIni = static::hariIni()->get();

        $hadir = $presensiHariIni->where('status', 'hadir')->count();
        $izin  = $presensiHariIni->where('status', 'izin')->count();
        $sakit = $presensiHariIni->where('status', 'sakit')->count();

        $sudahPresensi = $presensiHariIni->pluck('karyawan_id')->unique()->count();
        $alpha = max(0, $totalKaryawan - $sudahPresensi);

        return [
            'total_karyawan'  => $totalKaryawan,
            'hadir'           => $hadir,
            'izin'            => $izin,
            'sakit'           => $sakit,
            'alpha'           => $alpha,
            'persen_hadir'    => $totalKaryawan > 0
                                    ? round($hadir / $totalKaryawan * 100, 1)
                                    : 0,
        ];
    }

    public static function trendKehadiran(int $hari = 7): array
    {
        $result = [];
        for ($i = $hari - 1; $i >= 0; $i--) {
            $tanggal = Carbon::today()->subDays($i);
            $data    = static::whereDate('created_at', $tanggal)->get();

            $result[] = [
                'tanggal'  => $tanggal->format('Y-m-d'),
                'label'    => $tanggal->translatedFormat('d M'),
                'hadir'    => $data->where('status', 'hadir')->count(),
                'izin'     => $data->where('status', 'izin')->count(),
                'sakit'    => $data->where('status', 'sakit')->count(),
                'alpha'    => $data->where('status', 'alpha')->count(),
            ];
        }
        return $result;
    }
}