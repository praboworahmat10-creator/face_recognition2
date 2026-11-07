<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#C2410C">
    <meta name="mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="FaceID">
    <meta name="format-detection" content="telephone=no">
    <link rel="manifest" href="{{ asset('manifest.webmanifest') }}">
    <link rel="icon" type="image/png" sizes="192x192" href="{{ asset('icons/icon-192.png') }}">
    <link rel="apple-touch-icon" href="{{ asset('icons/icon-192.png') }}">
    <title>FaceID - Dashboard KOPPOSINDO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" crossorigin="" />
    <link rel="stylesheet" href="{{ asset('css/presensi.css') }}?v=5">
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" crossorigin=""></script>
</head>
<body>

    @php 
        $karyawan = \App\Models\Karyawan::where('email', Auth::user()->email ?? '')->first(); 
    @endphp

    {{-- SIDEBAR --}}
    <div class="modal-overlay" id="sidebarOverlay" onclick="toggleSidebar()" style="z-index: 90;"></div>
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo"><i class="fas fa-users"></i></div>
            <div class="brand-text">
                <h2>KOPPOSINDO</h2>
                <p>Koperasi Pegawai Pos Indonesia</p>
            </div>
        </div>
        <div class="sidebar-menu">
            <button class="nav-item active" id="navDashboard" onclick="switchView('dashboard')">
                <i class="fas fa-home"></i> Dashboard
            </button>
            <button class="nav-item" id="navPresensi" onclick="switchView('camera')">
                <i class="fas fa-fingerprint"></i> Presensi
            </button>
            <a href="{{ route('presensi.riwayat') }}" class="nav-item">
                <i class="fas fa-history"></i> Riwayat Presensi
            </a>
            <a href="{{ route('slip-gaji.index') }}" class="nav-item">
                <i class="fas fa-file-invoice-dollar"></i> Slip Gaji
            </a>
            
            <button class="nav-item" onclick="openModal('izin')">
                <i class="fas fa-file-alt"></i> Pengajuan Izin
            </button>
            <button class="nav-item" onclick="openModal('sakit')">
                <i class="fas fa-briefcase-medical"></i> Pengajuan Sakit
            </button>
            <button class="nav-item" onclick="openModal('cuti')">
                <i class="fas fa-calendar-days"></i> Pengajuan Cuti
            </button>

            <a href="{{ route('profil') }}" class="nav-item">
                <i class="fas fa-user"></i> Profil
            </a>
            <button type="button" class="nav-item" id="pwaInstallMenuBtn" onclick="showPwaInstall()">
                <i class="fas fa-download"></i> Install App
            </button>
            <form method="POST" action="{{ route('logout') }}" style="margin-top: auto;">
                @csrf
                <button type="submit" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </aside>

    {{-- MOBILE TOPBAR --}}
    <nav class="mobile-topbar">
        <button class="mobile-btn" onclick="toggleSidebar()" aria-label="Buka menu"><i class="fas fa-bars" aria-hidden="true"></i></button>
        <h1>Dashboard</h1>
        <span class="mobile-btn" aria-hidden="true"></span>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="main-content">

        {{-- ── DASHBOARD VIEW ── --}}
        <div id="viewDashboard" class="dashboard-view">
            
            <div class="header-section">
                <div class="user-intro">
                    @if($karyawan && $karyawan->foto)
                        <img src="{{ Storage::url($karyawan->foto) }}" alt="Foto {{ Auth::user()->name ?? 'karyawan' }}" class="user-avatar">
                    @else
                        <div class="user-avatar" style="background:#E2E8F0; display:flex; align-items:center; justify-content:center; font-size:1.5rem; color:#64748B; font-weight:bold;">
                            {{ strtoupper(substr(Auth::user()->name ?? 'U', 0, 1)) }}
                        </div>
                    @endif
                    <div>
                        <h2>Selamat Datang,</h2>
                        <h1>{{ Auth::user()->name ?? 'Karyawan' }}</h1>
                        <p>{{ optional($karyawan)->jabatan ?? 'Karyawan' }} - {{ optional($karyawan)->divisi ?? 'Departemen' }}</p>
                    </div>
                </div>
                <div class="time-box">
                    <p id="dateLabel">Senin, 1 Januari 2024</p>
                    <h1 id="liveClock">00:00:00</h1>
                    <p>WIB</p>
                </div>
            </div>

            @php
                $todayPresensi = \App\Models\Presensi::where('karyawan_id', optional($karyawan)->id)
                    ->whereDate('created_at', today())->latest()->first();
                $statusHariIni = $todayPresensi ? ucfirst($todayPresensi->status) : 'Belum Presensi';
                $isHadir = $todayPresensi && $todayPresensi->status === 'hadir';
                $waktuMasukDisplay = $todayPresensi && $todayPresensi->waktu_datang ? \Carbon\Carbon::parse($todayPresensi->waktu_datang)->format('H:i') : '-';
                $waktuPulangDisplay = $todayPresensi && $todayPresensi->waktu_pulang ? \Carbon\Carbon::parse($todayPresensi->waktu_pulang)->format('H:i') : '-';
            @endphp

            {{-- MOBILE STATUS CARDS --}}
            <div class="mobile-stats">
                <div class="mobile-stat-card">
                    <span class="mobile-stat-label">Status</span>
                    @if($isHadir)
                        <span class="mobile-stat-val success">Hadir</span>
                    @else
                        <span class="mobile-stat-val warning">{{ $statusHariIni }}</span>
                    @endif
                </div>
                <div class="mobile-stat-card">
                    <span class="mobile-stat-label">Masuk</span>
                    <span class="mobile-stat-val">{{ $waktuMasukDisplay !== '-' ? $waktuMasukDisplay : '—' }}</span>
                </div>
                <div class="mobile-stat-card">
                    <span class="mobile-stat-label">Pulang</span>
                    <span class="mobile-stat-val">{{ $waktuPulangDisplay !== '-' ? $waktuPulangDisplay : '—' }}</span>
                </div>
                <div class="mobile-stat-card">
                    <span class="mobile-stat-label">Sisa Masuk</span>
                    <span class="mobile-stat-val" id="sisaWaktuMobile">—</span>
                </div>
            </div>

            {{-- MOBILE QUICK ACTIONS --}}
            <div class="mobile-menus">
                <div class="m-menu-btn" onclick="switchView('camera')">
                    <div class="m-menu-icon" style="background:var(--primary-light); color:var(--primary);"><i class="fas fa-camera"></i></div>
                    <span>Presensi</span>
                </div>
                <a href="{{ route('presensi.riwayat') }}" class="m-menu-btn">
                    <div class="m-menu-icon" style="background:var(--success-light); color:var(--success);"><i class="fas fa-history"></i></div>
                    <span>Riwayat</span>
                </a>
                <div class="m-menu-btn" onclick="openModal('izin')">
                    <div class="m-menu-icon" style="background:var(--warning-light); color:var(--warning);"><i class="fas fa-file-alt"></i></div>
                    <span>Izin</span>
                </div>
                <div class="m-menu-btn" onclick="openModal('sakit')">
                    <div class="m-menu-icon" style="background:var(--danger-light); color:var(--danger);"><i class="fas fa-briefcase-medical"></i></div>
                    <span>Sakit</span>
                </div>
            </div>

            {{-- STATS GRID (Desktop) --}}
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Status Hari Ini</h3>
                    @if($isHadir)
                        <div class="badge-success">HADIR</div>
                    @else
                        <div class="badge-warning">{{ strtoupper($statusHariIni) }}</div>
                    @endif
                </div>
                <div class="stat-card">
                    <h3>Jam Masuk</h3>
                    <div class="val"><div class="stat-icon" style="background:var(--primary-light); color:var(--primary);"><i class="fas fa-clock"></i></div> {{ $waktuMasukDisplay !== '-' ? $waktuMasukDisplay : '07:30 - 09:00' }}</div>
                </div>
                <div class="stat-card">
                    <h3>Jam Pulang</h3>
                    <div class="val"><div class="stat-icon" style="background:var(--success-light); color:var(--success);"><i class="fas fa-clock"></i></div> {{ $waktuPulangDisplay !== '-' ? $waktuPulangDisplay : '16:30 - 18:00' }}</div>
                </div>
                <div class="stat-card">
                    <h3>Sisa Waktu Masuk</h3>
                    <div class="val"><div class="stat-icon" style="background:var(--danger-light); color:var(--danger);"><i class="fas fa-hourglass-half"></i></div> <span id="sisaWaktu">—</span></div>
                </div>
            </div>

            <div class="content-grid">
                <div class="card">
                    <h3>Riwayat Presensi Terakhir</h3>
                    <div style="overflow-x: auto;">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Masuk</th>
                                    <th>Pulang</th>
                                    <th>Status</th>
                                </tr>
                            </thead>
                            <tbody id="recentListTable">
                                @php
                                    $recentHarian = \App\Models\Presensi::where('karyawan_id', optional($karyawan)->id)
                                        ->orderBy('created_at','desc')->take(7)->get();
                                @endphp
                                @forelse($recentHarian as $rh)
                                    <tr>
                                        <td>{{ \Carbon\Carbon::parse($rh->waktu_datang ?? $rh->created_at)->format('d M Y') }}</td>
                                        <td>{{ $rh->waktu_datang ? \Carbon\Carbon::parse($rh->waktu_datang)->format('H:i:s') : '-' }}</td>
                                        <td>{{ $rh->waktu_pulang ? \Carbon\Carbon::parse($rh->waktu_pulang)->format('H:i:s') : '-' }}</td>
                                        <td>
                                            @if($rh->status == 'hadir')
                                                <span class="badge-table bt-success">Hadir</span>
                                            @elseif($rh->status == 'izin')
                                                <span class="badge-table" style="background:var(--warning-light);color:var(--warning);">Izin</span>
                                            @elseif($rh->status == 'sakit')
                                                <span class="badge-table bt-danger">Sakit</span>
                                            @elseif($rh->status == 'cuti')
                                                <span class="badge-table" style="background:var(--info-light);color:var(--info);">Cuti</span>
                                            @else
                                                <span class="badge-table bt-danger">{{ ucfirst($rh->status) }}</span>
                                            @endif
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="4" style="text-align:center; padding: 2rem 0; color:var(--text-muted);">Belum ada presensi hari ini</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <div class="card">
                    <h3>Lokasi Kantor</h3>
                    <div id="mapDashboard"></div>
                    <div class="map-info">
                        <strong>Petunjuk:</strong>
                        <ol style="margin-left:1rem; margin-top:0.5rem; display:flex; flex-direction:column; gap:6px;">
                            <li>Pastikan wajah berada dalam area kotak</li>
                            <li>Pastikan pencahayaan cukup</li>
                            <li>Pastikan berada dalam radius kantor</li>
                        </ol>
                    </div>
                    <button class="btn-presensi-besar" onclick="switchView('camera')"><i class="fas fa-fingerprint"></i> PRESENSI SEKARANG</button>
                </div>
            </div>

        </div>

        {{-- ── VIEW CAMERA ── --}}
        <div id="viewKamera" class="camera-view">
            <div class="camera-container">
                <div class="cam-header">
                    <i class="fas fa-expand"></i>
                    <p>Pastikan wajah Anda terlihat jelas dan berada dalam area kotak</p>
                </div>

                <div class="video-wrapper">
                    <video id="video" autoplay muted playsinline></video>
                    <canvas id="canvas"></canvas>
                    <div class="face-box" id="faceBox">
                        <div class="face-label" id="faceLabel">Wajah Terdeteksi</div>
                    </div>
                    {{-- Corner markers expected by old engine JS --}}
                    <div class="cam-corner tl" id="ccTL"></div>
                    <div class="cam-corner tr" id="ccTR"></div>
                    <div class="cam-corner bl" id="ccBL"></div>
                    <div class="cam-corner br" id="ccBR"></div>

                    {{-- Verified overlay expected by onFaceMatched --}}
                    <div class="verified-overlay" id="verifiedOverlay">
                        <div style="width:52px;height:52px;background:var(--success);border-radius:50%;display:flex;align-items:center;justify-content:center;color:white;font-size:1.5rem;"><i class="fas fa-check"></i></div>
                        <div style="font-size:0.875rem;font-weight:700;color:var(--success);background:white;padding:4px 14px;border-radius:20px;">Wajah Cocok!</div>
                    </div>

                    <div class="scan-overlay" id="scanOverlay">
                        <div class="scan-beam"></div>
                    </div>
                </div>

                <div class="metrics-dashboard">
                    <div class="metric-item">
                        <span class="metric-label">Liveness</span>
                        <span class="metric-value" id="metricLiveness">0%</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">Face Match</span>
                        <span class="metric-value" id="metricConf">0%</span>
                    </div>
                    <div class="metric-item">
                        <span class="metric-label">FPS</span>
                        <span class="metric-value" id="metricFPS">0</span>
                    </div>
                </div>

                <div class="scan-status-alert" id="scanStatusAlert" style="display:none;" role="status" aria-live="polite" aria-atomic="true">
                    <i class="fas fa-info-circle"></i> <span id="scanStatusText">Persiapan...</span>
                </div>

                <div class="identity-card" id="identityCard" style="display:none;">
                    <div class="id-avatar" id="identityAvatar">
                        <i class="fas fa-user"></i>
                        <img id="identityPhoto" src="" alt="" style="display:none;">
                    </div>
                    <div class="id-info">
                        <h3 id="identityName">-</h3>
                        <p id="identityNIK">-</p>
                        <div class="id-badges">
                            <span class="badge-divisi" id="identityDivisi">-</span>
                            <span class="badge-jabatan" id="identityJabatan">-</span>
                        </div>
                    </div>
                </div>

                {{-- Hidden elements required by older JS --}}
                <div style="display:none;">
                    <div id="metricBlink">0</div>
                    <div id="scanStatus" class="scan-status">-</div>
                    <div class="loc-icon" id="locIcon"><i class="fas fa-location-crosshairs"></i></div>
                    <div id="locTitle">Memeriksa...</div>
                    <div id="locSub">Mohon tunggu</div>
                    <div id="recentList"></div>
                </div>

                <div class="cam-loc-grid">
                    <div class="loc-box">
                        <h4>Lokasi (GPS)</h4>
                        <p id="camLat">Lat: -</p>
                        <p id="camLng">Lng: -</p>
                    </div>
                    <div class="loc-box">
                        <h4>Status Lokasi</h4>
                        <div class="loc-status" id="camLocStatus">
                            <i class="fas fa-spinner fa-spin" style="color:var(--warning);"></i>
                            <span style="color:var(--text-muted);">Memeriksa...</span>
                        </div>
                    </div>
                </div>

                <div style="margin-bottom: 1rem; border-top: 1px solid var(--border); padding-top: 1rem;">
                    <div style="display:flex; justify-content:space-between; margin-bottom: 4px; font-size: 0.85rem; font-weight:600;">
                        <span>Akurasi Liveness & Wajah</span>
                        <span id="confVal" style="color:var(--primary);">0%</span>
                    </div>
                    <div style="height:6px;background:var(--border);border-radius:3px;overflow:hidden;">
                        <div id="confFill" style="height:100%;width:0%;background:var(--primary);transition:width 0.3s;"></div>
                    </div>
                </div>

                <div class="cam-actions-sticky">
                    <div class="cam-actions-row">
                        <button class="btn-presensi-besar" id="btnScanMasuk" onclick="startScan('masuk')" disabled><i class="fas fa-spinner fa-spin"></i> Menyiapkan AI...</button>
                        <button class="btn-presensi-besar btn-pulang" id="btnScanPulang" onclick="startScan('pulang')" disabled><i class="fas fa-spinner fa-spin"></i></button>
                    </div>
                    <button class="btn-batal" onclick="switchView('dashboard')">Batal</button>
                </div>
            </div>
        </div>

    </main>

    {{-- MOBILE BOTTOM NAV --}}
    <nav class="mobile-bottom-nav" id="mobileBottomNav" aria-label="Navigasi utama">
        <button type="button" class="bottom-nav-item active" id="bnDashboard" onclick="switchView('dashboard')">
            <i class="fas fa-home"></i><span>Beranda</span>
        </button>
        <button type="button" class="bottom-nav-item" id="bnPresensi" onclick="switchView('camera')">
            <i class="fas fa-fingerprint"></i><span>Presensi</span>
        </button>
        <a href="{{ route('presensi.riwayat') }}" class="bottom-nav-item">
            <i class="fas fa-history"></i><span>Riwayat</span>
        </a>
        <button type="button" class="bottom-nav-item" onclick="toggleSidebar()">
            <i class="fas fa-ellipsis-h"></i><span>Menu</span>
        </button>
    </nav>

    {{-- FAB Presensi Cepat (mobile dashboard) --}}
    <button type="button" class="fab-presensi" id="fabPresensi" onclick="switchView('camera')" aria-label="Presensi sekarang">
        <i class="fas fa-camera"></i>
    </button>

    {{-- MODALS PENGUJUAN (Tetap dipertahankan untuk form) --}}
    <!-- Modal Izin -->
    <div class="modal-overlay" id="modalIzin" style="z-index: 9999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pengajuan Izin</h3>
                <button class="modal-close" onclick="closeModal('izin')" aria-label="Tutup"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('presensi.izin') }}" method="POST" id="formIzin">
                    @csrf
                    <input type="hidden" name="jenis" value="izin">
                    <div class="form-group">
                        <label class="form-label">Tanggal Izin</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alasan Izin</label>
                        <textarea name="keterangan" class="form-control" rows="3" placeholder="Tuliskan alasan..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-batal" style="width:auto; margin:0;" onclick="closeModal('izin')">Batal</button>
                <button class="btn-presensi-besar" style="width:auto; padding:0.75rem 1.5rem;" onclick="document.getElementById('formIzin').submit()">Kirim Izin</button>
            </div>
        </div>
    </div>
    
    
    <!-- Modal Sakit -->
    <div class="modal-overlay" id="modalSakit" style="z-index: 9999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pengajuan Sakit</h3>
                <button class="modal-close" onclick="closeModal('sakit')" aria-label="Tutup"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('presensi.sakit') }}" method="POST" id="formSakit" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="jenis" value="sakit">
                    <div class="form-group">
                        <label class="form-label">Tanggal Sakit</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload Surat Dokter</label>
                        <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan / Penyakit</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Sakit demam, flu, dll..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-batal" style="width:auto; margin:0;" onclick="closeModal('sakit')">Batal</button>
                <button class="btn-presensi-besar" style="width:auto; padding:0.75rem 1.5rem; background:var(--danger);" onclick="document.getElementById('formSakit').submit()">Kirim Surat Sakit</button>
            </div>
        </div>
    </div>

    <!-- Modal Cuti -->
    <div class="modal-overlay" id="modalCuti" style="z-index: 9999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pengajuan Cuti</h3>
                <button class="modal-close" onclick="closeModal('cuti')" aria-label="Tutup"><i class="fas fa-times" aria-hidden="true"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('presensi.cuti') }}" method="POST" id="formCuti">
                    @csrf
                    <input type="hidden" name="jenis" value="cuti">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alasan Cuti</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Cuti tahunan, urusan keluarga..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-batal" style="width:auto; margin:0;" onclick="closeModal('cuti')">Batal</button>
                <button class="btn-presensi-besar" style="width:auto; padding:0.75rem 1.5rem; background:var(--primary);" onclick="document.getElementById('formCuti').submit()">Ajukan Cuti</button>
            </div>
        </div>
    </div>

    <div class="toast-wrap" id="toastWrap"></div>

    {{-- ══════════════════════════════════════════════════════════
         OVERLAY PROSES — 3 keadaan terpisah (Fase 1)
         1. konfirmasi  2. menyimpan  3. hasil (berhasil / gagal)
         Tidak ada klaim "berhasil" sebelum server mengonfirmasi.
         ══════════════════════════════════════════════════════════ --}}

    {{-- 1. KONFIRMASI sebelum simpan --}}
    <div class="process-overlay" id="confirmOverlay" role="dialog" aria-modal="true" aria-labelledby="confirmTitle">
        <div class="process-box">
            <h2 class="process-title" id="confirmTitle">Simpan presensi masuk?</h2>
            <p class="process-sub" id="confirmSub">Wajah dan lokasi Anda sudah terverifikasi.</p>

            <div class="confirm-preview">
                <img id="confirmPhoto" src="" alt="Foto yang baru diambil">
                <div>
                    <h4 id="confirmName">—</h4>
                    <p id="confirmMeta">—</p>
                </div>
            </div>

            <dl class="result-rows">
                <div class="result-row">
                    <dt>Waktu</dt>
                    <dd class="mono" id="confirmTime">—</dd>
                </div>
                <div class="result-row">
                    <dt>Lokasi</dt>
                    <dd id="confirmLoc">—</dd>
                </div>
            </dl>

            <div class="process-actions">
                <button type="button" class="btn-presensi-besar" id="confirmYes">
                    <i class="fas fa-check" aria-hidden="true"></i> <span id="confirmYesLabel">Ya, Simpan</span>
                </button>
                <button type="button" class="btn-batal" id="confirmNo">Batal</button>
            </div>
        </div>
    </div>

    {{-- 2. SEDANG MENYIMPAN --}}
    <div class="process-overlay" id="savingOverlay" role="status" aria-live="polite">
        <div class="process-box">
            <div class="process-spinner" aria-hidden="true"></div>
            <h2 class="process-title">Menyimpan presensi…</h2>
            <p class="process-sub">Mohon tunggu, jangan tutup halaman ini.</p>
        </div>
    </div>

    {{-- 3. HASIL — diisi oleh JS, ditutup manual oleh pengguna --}}
    <div class="process-overlay" id="resultOverlay" role="dialog" aria-modal="true" aria-labelledby="resultTitle">
        <div class="process-box">
            <div class="result-icon" id="resultIcon" aria-hidden="true"><i class="fas fa-check"></i></div>
            <h2 class="process-title" id="resultTitle">—</h2>
            <p class="process-sub" id="resultSub">—</p>
            <div id="resultBody"></div>
            <div class="process-actions" id="resultActions"></div>
        </div>
    </div>

    {{-- Kompatibilitas: elemen lama masih dirujuk sebagian JS --}}
    <div id="autoSubmitCard" style="display:none;" aria-hidden="true"></div>
<script src="{{ asset('js/face-api.js') }}"></script>
<script src="{{ asset('js/FaceRecognitionEngine.js') }}"></script>
<script>
window.PRESENSI_CONFIG = {
    kantorLat: {{ config('kantor.latitude') ?? 0 }},
    kantorLng: {{ config('kantor.longitude') ?? 0 }},
    maxRadius: {{ config('kantor.radius_meter') ?? 30 }},
    nikLogin: @json(optional($karyawan)->nik ?? ''),
    matchThreshold: {{ config('face.match_threshold', 0.55) }},
    matchMargin: {{ config('face.match_margin', 0.08) }},
    detectionMinConfidence: {{ config('face.detection_min_confidence', 60) }},
};
</script>
<script src="{{ asset('js/presensi.js') }}?v=6"></script>
<script src="{{ asset('js/pwa.js') }}"></script>
</body>
</html>