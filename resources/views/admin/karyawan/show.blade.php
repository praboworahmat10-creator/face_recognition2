@extends('layouts.admin')

@section('title', 'Detail Karyawan')
@section('page-title', 'Detail Karyawan')

@push('styles')
<style>
    .page-hd {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 20px;
    }
    .page-hd h2 { font-size:18px; font-weight:700; color:var(--text); letter-spacing:-0.3px; }
    .page-hd p  { font-size:12.5px; color:var(--text-3); margin-top:2px; }

    .btn {
        display: inline-flex; align-items: center; gap:6px;
        padding: 8px 16px; border-radius:9px;
        font-family:'DM Sans',sans-serif; font-size:13px; font-weight:600;
        border:none; cursor:pointer; transition:all 0.15s; text-decoration:none;
    }
    .btn-primary { background:var(--primary); color:white; }
    .btn-primary:hover { background:#ea580c; }
    .btn-secondary { background:var(--surface-2); color:var(--text-2); border:1px solid var(--border); }
    .btn-secondary:hover { border-color:var(--primary); color:var(--primary); }
    .btn-sm { padding:5px 11px; font-size:12px; border-radius:7px; }

    .grid-container {
        display: grid;
        grid-template-columns: 320px 1fr;
        gap: 20px;
    }
    @media (max-width: 900px) {
        .grid-container {
            grid-template-columns: 1fr;
        }
    }

    .card {
        background:var(--surface); border:1px solid var(--border);
        border-radius:var(--radius); box-shadow:var(--shadow); padding: 24px;
        margin-bottom: 20px;
    }

    .profile-header {
        text-align: center;
        margin-bottom: 24px;
    }

    .avatar-large {
        width: 96px; height: 96px; border-radius: 50%;
        background: var(--primary-light); color: var(--primary);
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 32px; font-weight: 700; margin-bottom: 14px;
        border: 4px solid var(--surface-2);
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }

    .profile-name {
        font-size: 18px;
        font-weight: 700;
        color: var(--text);
        margin-bottom: 4px;
    }

    .profile-email {
        font-size: 13px;
        color: var(--text-3);
        margin-bottom: 12px;
    }

    .badge-status {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }
    .badge-status.aktif   { background: var(--emerald-light); color: var(--emerald); }
    .badge-status.cuti    { background: var(--amber-light);   color: var(--amber); }
    .badge-status.resign  { background: var(--rose-light);    color: var(--rose); }
    .badge-status.suspend { background: var(--border);        color: var(--text-3); }

    .info-list {
        border-top: 1px solid var(--border);
        padding-top: 18px;
    }

    .info-item {
        display: flex;
        justify-content: space-between;
        margin-bottom: 12px;
        font-size: 13px;
    }

    .info-label {
        color: var(--text-3);
    }

    .info-val {
        font-weight: 600;
        color: var(--text-2);
    }

    .stat-box-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
        margin-bottom: 24px;
    }
    @media (max-width: 500px) {
        .stat-box-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    .stat-box {
        background: var(--surface-2);
        border: 1px solid var(--border);
        border-radius: 8px;
        padding: 14px;
        text-align: center;
    }

    .stat-box-val {
        font-size: 20px;
        font-weight: 700;
        color: var(--text);
    }

    .stat-box-label {
        font-size: 12px;
        color: var(--text-3);
        margin-top: 2px;
    }

    .face-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(80px, 1fr));
        gap: 8px;
        margin-top: 14px;
    }

    .face-img {
        width: 100%;
        aspect-ratio: 1;
        object-fit: cover;
        border-radius: 8px;
        border: 1px solid var(--border);
    }
</style>
@endpush

@section('content')

<div class="page-hd">
    <div>
        <h2>Detail Karyawan</h2>
        <p>Lihat profil lengkap, status pendaftaran wajah, dan statistik presensi</p>
    </div>
    <div>
        <a href="{{ route('admin.karyawan.index') }}" class="btn btn-secondary">
            <i class="fas fa-arrow-left"></i> Kembali
        </a>
    </div>
</div>

<div class="grid-container">
    <!-- Profil Card -->
    <div>
        <div class="card">
            <div class="profile-header">
                <div class="avatar-large">{{ $karyawan->inisial }}</div>
                <h3 class="profile-name">{{ $karyawan->nama }}</h3>
                <p class="profile-email">{{ $karyawan->email }}</p>
                <span class="badge-status {{ $karyawan->status_karyawan }}">
                    {{ ucfirst($karyawan->status_karyawan) }}
                </span>
            </div>

            <div class="info-list">
                <div class="info-item">
                    <span class="info-label">NIK</span>
                    <span class="info-val" style="font-family:'DM Mono',monospace;color:var(--primary);">{{ $karyawan->nik }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Divisi</span>
                    <span class="info-val">{{ $karyawan->divisi }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Jabatan</span>
                    <span class="info-val">{{ $karyawan->jabatan }}</span>
                </div>
                <div class="info-item">
                    <span class="info-label">Terdaftar Sejak</span>
                    <span class="info-val">{{ $karyawan->created_at->format('d/m/Y') }}</span>
                </div>
            </div>

            <div style="margin-top: 20px; display: flex; gap: 8px;">
                <a href="{{ route('admin.karyawan.edit', $karyawan) }}" class="btn btn-secondary btn-sm" style="flex:1; justify-content:center;">
                    <i class="fas fa-edit"></i> Edit Profil
                </a>
            </div>
        </div>

        <!-- Face Data Card -->
        <div class="card">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:12px; color:var(--text);">Data Biometrik Wajah</h3>
            
            <div class="info-list" style="border:none; padding:0;">
                <div class="info-item">
                    <span class="info-label">Status Wajah</span>
                    <span class="info-val" style="color:{{ $karyawan->face_enrolled ? 'var(--emerald)' : 'var(--rose)' }}">
                        {{ $karyawan->face_enrolled ? 'Terdaftar' : 'Belum Terdaftar' }}
                    </span>
                </div>
                @if($karyawan->face_enrolled)
                    <div class="info-item">
                        <span class="info-label">Jumlah Sampel</span>
                        <span class="info-val">{{ $karyawan->face_sample_count }} Foto</span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Tanggal Enroll</span>
                        <span class="info-val">{{ $karyawan->face_enrolled_at->format('d/m/Y H:i') }}</span>
                    </div>
                @endif
            </div>

            @if($karyawan->face_enrolled && count($karyawan->face_photos_array) > 0)
                <div class="face-grid">
                    @foreach($karyawan->face_photos_array as $photo)
                        <img src="{{ asset('storage/' . $photo) }}" class="face-img" alt="Face Sample">
                    @endforeach
                </div>
            @endif

            <div style="margin-top: 16px;">
                @if($karyawan->face_enrolled)
                    <form method="POST" action="{{ route('admin.karyawan.delete-face', $karyawan) }}" onsubmit="return confirm('Apakah Anda yakin ingin menghapus data biometrik wajah karyawan ini?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-secondary btn-sm" style="width:100%; justify-content:center; color:var(--rose); border-color:var(--rose-light);">
                            <i class="fas fa-trash-alt"></i> Hapus Data Wajah
                        </button>
                    </form>
                @else
                    <a href="{{ route('admin.karyawan.enroll-face.new', ['karyawan_id' => $karyawan->id]) }}" class="btn btn-primary btn-sm" style="width:100%; justify-content:center;">
                        <i class="fas fa-camera"></i> Enroll Wajah Sekarang
                    </a>
                @endif
            </div>
        </div>
    </div>

    <!-- Statistik & Kehadiran Card -->
    <div>
        <div class="card">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:16px; color:var(--text);">Statistik Bulan Ini ({{ now()->translatedFormat('F Y') }})</h3>
            
            <div class="stat-box-grid">
                <div class="stat-box">
                    <div class="stat-box-val" style="color:var(--emerald);">{{ $statistik['hadir'] }}</div>
                    <div class="stat-box-label">Hadir</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-val" style="color:var(--amber);">{{ $statistik['izin'] }}</div>
                    <div class="stat-box-label">Izin</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-val" style="color:var(--sky);">{{ $statistik['sakit'] }}</div>
                    <div class="stat-box-label">Sakit</div>
                </div>
                <div class="stat-box">
                    <div class="stat-box-val" style="color:var(--rose);">{{ $statistik['alpha'] }}</div>
                    <div class="stat-box-label">Alpha</div>
                </div>
            </div>

            <div style="background:var(--surface-2); padding:14px; border-radius:8px; border:1px solid var(--border); display:flex; align-items:center; justify-content:space-between;">
                <div>
                    <span style="font-size:12.5px; color:var(--text-3); display:block;">Persentase Kehadiran</span>
                    <span style="font-size:16px; font-weight:700; color:var(--text-2);">{{ $statistik['persen_hadir'] }}%</span>
                </div>
                <div style="font-size:12.5px; color:var(--text-3);">
                    Total Hari Kerja tercatat: <strong>{{ $statistik['total'] }} hari</strong>
                </div>
            </div>
        </div>

        <div class="card">
            <h3 style="font-size:14px; font-weight:700; margin-bottom:16px; color:var(--text);">Riwayat Presensi Terbaru</h3>
            
            <div style="overflow-x:auto;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid var(--border);">
                            <th style="padding:10px 0; text-align:left; font-size:11px; color:var(--text-3);">Tanggal</th>
                            <th style="padding:10px 0; text-align:left; font-size:11px; color:var(--text-3);">Jam Masuk</th>
                            <th style="padding:10px 0; text-align:left; font-size:11px; color:var(--text-3);">Jam Pulang</th>
                            <th style="padding:10px 0; text-align:left; font-size:11px; color:var(--text-3);">Confidence</th>
                            <th style="padding:10px 0; text-align:left; font-size:11px; color:var(--text-3);">Jarak</th>
                            <th style="padding:10px 0; text-align:left; font-size:11px; color:var(--text-3);">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($karyawan->presensi()->orderBy('created_at', 'desc')->take(10)->get() as $p)
                        <tr style="border-bottom:1px solid var(--border);">
                            <td style="padding:12px 0; font-size:13px; color:var(--text-2);">{{ $p->tanggal }}</td>
                            <td style="padding:12px 0; font-size:13px; font-family:'DM Mono',monospace;">{{ $p->waktu_datang_format }}</td>
                            <td style="padding:12px 0; font-size:13px; font-family:'DM Mono',monospace;">{{ $p->waktu_pulang_format }}</td>
                            <td style="padding:12px 0; font-size:13px; font-family:'DM Mono',monospace;">
                                {{ $p->face_confidence ? ($p->confidence_pct . '%') : '—' }}
                            </td>
                            <td style="padding:12px 0; font-size:13px; color:var(--text-2);">{{ $p->jarak_label }}</td>
                            <td style="padding:12px 0;">
                                <span style="font-size:11px; font-weight:600; padding:2px 6px; border-radius:10px;
                                    @if($p->status == 'hadir') background:var(--emerald-light); color:var(--emerald);
                                    @elseif($p->status == 'izin') background:var(--amber-light); color:var(--amber);
                                    @elseif($p->status == 'sakit') background:var(--sky-light); color:var(--sky);
                                    @else background:var(--rose-light); color:var(--rose); @endif">
                                    {{ ucfirst($p->status) }}
                                </span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" style="padding:24px 0; text-align:center; color:var(--text-3); font-size:13px;">
                                Belum ada riwayat presensi.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@endsection
