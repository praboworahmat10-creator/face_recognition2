<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class Karyawan extends Authenticatable
{
    use HasFactory, SoftDeletes;

    protected $table = 'karyawans';

    protected $fillable = [
        'nik',
        'nama',
        'email',
        'password',
        'remember_token', 
        'jenis_kelamin',
        'tanggal_lahir',
        'divisi',
        'jabatan',
        'status_karyawan',
        'foto',
        'face_descriptors',
        'face_photos',
        'face_enrolled_at',
        'face_sample_count',
    ];

    protected $casts = [
        'face_enrolled_at'  => 'datetime',
        'tanggal_lahir'     => 'date',
        'face_sample_count' => 'integer',
    ];

    protected $hidden = [
        'face_descriptors',
        'deleted_at',
        'password',
        'remember_token',
    ];

    // ─────────────────────────────────────────────
    // RELASI
    // ─────────────────────────────────────────────
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function presensi()
    {
        return $this->hasMany(Presensi::class, 'karyawan_id');
    }

    public function presensiHariIni()
    {
        return $this->hasOne(Presensi::class, 'karyawan_id')
                    ->whereDate('created_at', today());
    }

    public function presensibulanIni()
    {
        return $this->hasMany(Presensi::class, 'karyawan_id')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year);
    }

    public function slipGaji()
    {
        return $this->hasMany(SlipGaji::class, 'karyawan_id')
                    ->orderByDesc('tahun')
                    ->orderByDesc('bulan');
    }

    // ─────────────────────────────────────────────
    // ACCESSORS
    // ─────────────────────────────────────────────
    public function getFaceEnrolledAttribute(): bool
    {
        return !is_null($this->face_descriptors) && $this->face_sample_count > 0;
    }

    public function getFotoUrlAttribute(): string
    {
        if ($this->foto) {
            return asset('storage/' . $this->foto);
        }
        return asset('images/default-avatar.png');
    }

    public function getFacePhotosArrayAttribute(): array
    {
        return json_decode($this->face_photos ?? '[]', true);
    }

    public function getFaceDescriptorsArrayAttribute(): array
    {
        return json_decode($this->face_descriptors ?? '[]', true);
    }

    public function getFacePhotoCountAttribute(): int
    {
        $photos = json_decode($this->face_photos ?? '[]', true);
        return count($photos);
    }

    public function getFaceStatusLabelAttribute(): string
    {
        if ($this->face_enrolled) {
            return '✓ Terdaftar (' . $this->face_sample_count . ' sampel)';
        }
        return '✗ Belum Daftar';
    }

    public function getInisialAttribute(): string
    {
        $words = explode(' ', $this->nama);
        $inisial = '';
        foreach (array_slice($words, 0, 2) as $word) {
            $inisial .= strtoupper(substr($word, 0, 1));
        }
        return $inisial;
    }

    // ─────────────────────────────────────────────
    // SCOPES
    // ─────────────────────────────────────────────
    public function scopeAktif($query)
    {
        return $query->where('status_karyawan', 'aktif');
    }

    public function scopeEnrolled($query)
    {
        return $query->whereNotNull('face_descriptors')
                     ->where('face_sample_count', '>', 0);
    }

    public function scopeDivisi($query, string $divisi)
    {
        return $query->where('divisi', $divisi);
    }

    public function scopeJabatan($query, string $jabatan)
    {
        return $query->where('jabatan', $jabatan);
    }

    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nik', 'like', "%{$keyword}%")
              ->orWhere('nama', 'like', "%{$keyword}%")
              ->orWhere('email', 'like', "%{$keyword}%");
        });
    }

    // ─────────────────────────────────────────────
    // METHODS
    // ─────────────────────────────────────────────
    public function statistikKehadiran(?int $bulan = null, ?int $tahun = null): array
    {
        $query = $this->presensi();

        if ($bulan) {
            $query->whereMonth('created_at', $bulan);
        }
        if ($tahun) {
            $query->whereYear('created_at', $tahun);
        }

        $data = $query->get();

        $hadir = $data->where('status', 'hadir')->count();
        $izin  = $data->where('status', 'izin')->count();
        $sakit = $data->where('status', 'sakit')->count();
        $alpha = $data->where('status', 'alpha')->count();
        $total = $data->count();

        return [
            'hadir'          => $hadir,
            'izin'           => $izin,
            'sakit'          => $sakit,
            'alpha'          => $alpha,
            'total'          => $total,
            'persen_hadir'   => $total > 0 ? round($hadir / $total * 100, 1) : 0,
        ];
    }

    public function sudahAbsenHariIni(): bool
    {
        return $this->presensi()
                    ->whereDate('created_at', today())
                    ->where('status', 'hadir')
                    ->exists();
    }

    public function simpanFaceData(array $descriptors, array $photoPaths): void
    {
        $this->update([
            'face_descriptors'  => json_encode($descriptors),
            'face_photos'       => json_encode($photoPaths),
            'face_enrolled_at'  => now(),
            'face_sample_count' => count($photoPaths),
        ]);
    }

    public function hapusFaceData(): void
    {
        $this->update([
            'face_descriptors'  => null,
            'face_photos'       => null,
            'face_enrolled_at'  => null,
            'face_sample_count' => 0,
            'foto'              => null,
        ]);
    }

    public function getAuthIdentifierName()
    {
        return 'nik';
    }

    public function getAuthPassword()
    {
        return $this->password;
    }
}