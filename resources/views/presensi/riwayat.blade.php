<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Riwayat Presensi — KOPPOSINDO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC;
            --surface: #FFFFFF;
            --border: #E2E8F0;
            --text-main: #1E293B;
            --text-muted: #64748B;
            --primary: #3B82F6;
            --primary-hover: #2563EB;
            --primary-light: #EFF6FF;
            --sidebar-bg: #0F172A;
            --success: #10B981;
            --success-light: #D1FAE5;
            --warning: #F59E0B;
            --warning-light: #FEF3C7;
            --danger: #EF4444;
            --danger-light: #FEE2E2;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --radius-md: 10px;
            --radius-lg: 16px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-main); min-height: 100vh; -webkit-font-smoothing: antialiased; }

        /* SIDEBAR */
        .sidebar {
            position: fixed; top: 0; left: 0; bottom: 0; width: 260px;
            background: var(--sidebar-bg); color: white; z-index: 100;
            display: flex; flex-direction: column; overflow-y: auto;
        }
        .sidebar-brand { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .brand-logo { width: 36px; height: 36px; background: #EA580C; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; flex-shrink: 0; }
        .brand-text h2 { font-size: 1rem; font-weight: 700; color: white; margin-bottom: 2px; }
        .brand-text p { font-size: 0.6rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; }

        .sidebar-menu { padding: 1.25rem 1rem; flex: 1; display: flex; flex-direction: column; gap: 2px; }
        .nav-label { font-size: 0.65rem; font-weight: 700; color: rgba(148,163,184,0.5); text-transform: uppercase; letter-spacing: 0.08em; padding: 0 0.5rem; margin: 1rem 0 0.4rem; }
        .nav-item {
            display: flex; align-items: center; gap: 10px; padding: 0.75rem 0.875rem;
            color: #94A3B8; text-decoration: none; font-weight: 500; font-size: 0.875rem;
            border-radius: 8px; transition: all 0.15s; border: none; background: none;
            width: 100%; cursor: pointer; text-align: left;
        }
        .nav-item i { width: 18px; text-align: center; font-size: 1rem; flex-shrink: 0; }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: #e2e8f0; }
        .nav-item.active { background: var(--primary); color: white; font-weight: 600; }
        .nav-item.logout { color: #EF4444; margin-top: auto; }
        .nav-item.logout:hover { background: rgba(239,68,68,0.1); }
        .sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,0.07); }

        /* MOBILE TOPBAR */
        .mobile-topbar { display: none; background: var(--primary); color: white; height: 60px; padding: 0 1.25rem; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 90; }
        .mobile-btn { background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; }

        /* MAIN */
        .main-content { margin-left: 260px; padding: 2rem; }

        /* TOP CLOCK BAR */
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .top-bar h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; }
        .top-bar p { font-size: 0.85rem; color: var(--text-muted); margin-top: 2px; }
        .time-pill { font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; font-weight: 700; color: var(--primary); background: var(--primary-light); padding: 0.5rem 1rem; border-radius: 10px; border: 1px solid rgba(59,130,246,0.15); }

        /* SUMMARY CARDS */
        .summary-grid { display: grid; grid-template-columns: repeat(5, 1fr); gap: 1rem; margin-bottom: 2rem; }
        .summary-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.25rem; text-align: center; box-shadow: var(--shadow-sm); }
        .summary-val { font-size: 1.75rem; font-weight: 800; margin-bottom: 4px; }
        .summary-lbl { font-size: 0.75rem; color: var(--text-muted); font-weight: 600; text-transform: uppercase; letter-spacing: 0.05em; }

        /* TAB BUTTONS */
        .tab-bar { display: flex; gap: 8px; margin-bottom: 1.5rem; }
        .tab-btn { padding: 0.65rem 1.25rem; border-radius: 8px; font-weight: 600; font-size: 0.9rem; text-decoration: none; transition: all 0.15s; border: 1px solid var(--border); background: var(--surface); color: var(--text-muted); }
        .tab-btn.active { background: var(--primary); color: white; border-color: var(--primary); }

        /* TABLE CARD */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .card-head { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; background: #FAFAFA; }
        .card-head-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; background: var(--primary-light); color: var(--primary); }
        .card-head h3 { font-size: 0.95rem; font-weight: 700; }

        table { width: 100%; border-collapse: collapse; font-size: 0.875rem; }
        thead tr { background: #FAFAFA; border-bottom: 2px solid var(--border); }
        th { padding: 0.875rem 1.5rem; text-align: left; font-weight: 700; font-size: 0.8rem; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; }
        td { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); font-weight: 500; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: #FAFAFE; }

        .badge { padding: 0.3rem 0.75rem; border-radius: 6px; font-weight: 700; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.04em; }
        .badge-hadir { background: var(--success-light); color: var(--success); }
        .badge-izin { background: var(--warning-light); color: var(--warning); }
        .badge-sakit { background: var(--danger-light); color: var(--danger); }
        .badge-cuti { background: var(--primary-light); color: var(--primary); }
        .badge-alpha { background: #FEE2E2; color: #DC2626; }

        /* Filter form */
        .filter-form { display: flex; gap: 10px; margin-bottom: 1.5rem; }
        .filter-input { padding: 0.65rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: 'Inter', sans-serif; font-size: 0.875rem; background: var(--surface); outline: none; }
        .filter-input:focus { border-color: var(--primary); }
        .btn-filter { padding: 0.65rem 1.25rem; background: var(--primary); color: white; border: none; border-radius: 8px; font-weight: 600; font-size: 0.875rem; cursor: pointer; }

        /* Modal Overlay */
        .modal-overlay { position: fixed; inset: 0; background: rgba(15,23,42,0.6); backdrop-filter: blur(4px); z-index: 200; display: none; align-items: center; justify-content: center; }
        .modal-overlay.show { display: flex; }
        .modal-content { background: var(--surface); width: 90%; max-width: 500px; border-radius: var(--radius-lg); box-shadow: 0 20px 50px rgba(0,0,0,0.15); overflow: hidden; }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { font-size: 1.05rem; font-weight: 700; }
        .modal-close { background: none; border: none; font-size: 1.1rem; color: var(--text-muted); cursor: pointer; }
        .modal-body { padding: 1.5rem; }
        .modal-footer { padding: 1rem 1.5rem; border-top: 1px solid var(--border); display: flex; justify-content: flex-end; gap: 10px; }
        .form-group { margin-bottom: 1.1rem; }
        .form-label { display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main); }
        .form-control { width: 100%; padding: 0.75rem 1rem; border: 1px solid var(--border); border-radius: 8px; font-family: inherit; font-size: 0.875rem; background: var(--bg-color); outline: none; }
        .form-control:focus { border-color: var(--primary); background: var(--surface); }
        .btn-modal { padding: 0.75rem 1.25rem; border-radius: 8px; font-weight: 700; font-size: 0.875rem; border: none; cursor: pointer; }
        .btn-cancel { background: var(--bg-color); border: 1px solid var(--border); color: var(--text-muted); }
        .btn-submit-izin { background: var(--primary); color: white; }
        .btn-submit-sakit { background: var(--danger); color: white; }
        .btn-submit-cuti { background: var(--success); color: white; }

        /* Overlay sidebar mobile */
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }
        .sidebar-overlay.active { display: block; }
        .sidebar.active { transform: translateX(0) !important; }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .mobile-topbar { display: flex; }
            .main-content { margin-left: 0; padding: 1rem; }
            .summary-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>

{{-- SIDEBAR --}}
<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo"><i class="fas fa-users" style="color:white;"></i></div>
        <div class="brand-text">
            <h2>KOPPOSINDO</h2>
            <p>Koperasi Pegawai Pos Indonesia</p>
        </div>
    </div>

    <div class="sidebar-menu">
        <div class="nav-label">Menu Utama</div>
        <a href="{{ route('presensi') }}" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="{{ route('presensi') }}" class="nav-item"><i class="fas fa-fingerprint"></i> Presensi</a>
        <a href="{{ route('presensi.riwayat') }}" class="nav-item active"><i class="fas fa-history"></i> Riwayat Presensi</a>
        <a href="{{ route('profil') }}" class="nav-item"><i class="fas fa-user"></i> Profil</a>

        <div class="nav-label">Pengajuan</div>
        <button class="nav-item" onclick="openModal('izin')"><i class="fas fa-file-alt"></i> Pengajuan Izin</button>
        <button class="nav-item" onclick="openModal('sakit')"><i class="fas fa-briefcase-medical"></i> Pengajuan Sakit</button>
        <button class="nav-item" onclick="openModal('cuti')"><i class="fas fa-calendar-days"></i> Pengajuan Cuti</button>
    </div>

    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
</aside>

{{-- MOBILE TOPBAR --}}
<nav class="mobile-topbar">
    <button class="mobile-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <span style="font-weight: 700; font-size: 1rem;">Riwayat Presensi</span>
    <span></span>
</nav>

{{-- MAIN --}}
<main class="main-content">
    <div class="top-bar">
        <div>
            <h1>Riwayat Presensi</h1>
            <p>Lihat detail kehadiran harian dan ringkasan bulanan Anda</p>
        </div>
        <div class="time-pill" id="liveClock">00:00:00</div>
    </div>

    {{-- SUMMARY CARDS (only on bulanan) --}}
    @if($tab == 'bulanan')
    <div class="summary-grid">
        <div class="summary-card">
            <div class="summary-val" style="color:var(--success);">{{ $summary['hadir'] }}</div>
            <div class="summary-lbl">Hadir</div>
        </div>
        <div class="summary-card">
            <div class="summary-val" style="color:var(--warning);">{{ $summary['izin'] }}</div>
            <div class="summary-lbl">Izin</div>
        </div>
        <div class="summary-card">
            <div class="summary-val" style="color:var(--danger);">{{ $summary['sakit'] }}</div>
            <div class="summary-lbl">Sakit</div>
        </div>
        <div class="summary-card">
            <div class="summary-val" style="color:var(--primary);">{{ $summary['cuti'] }}</div>
            <div class="summary-lbl">Cuti</div>
        </div>
        <div class="summary-card">
            <div class="summary-val" style="color:#DC2626;">{{ $summary['alpha'] }}</div>
            <div class="summary-lbl">Alpha</div>
        </div>
    </div>
    @endif

    {{-- TAB BUTTONS --}}
    <div class="tab-bar">
        <a href="?tab=harian" class="tab-btn {{ $tab == 'harian' ? 'active' : '' }}">
            <i class="fas fa-calendar-day"></i> Riwayat Harian
        </a>
        <a href="?tab=bulanan" class="tab-btn {{ $tab == 'bulanan' ? 'active' : '' }}">
            <i class="fas fa-chart-bar"></i> Rekap Bulanan
        </a>
    </div>

    @if($tab == 'bulanan')
    <form method="GET" class="filter-form">
        <input type="hidden" name="tab" value="bulanan">
        <input type="month" name="bulan" value="{{ $bulan }}" class="filter-input">
        <button type="submit" class="btn-filter"><i class="fas fa-search"></i> Filter</button>
    </form>
    @endif

    {{-- TABLE --}}
    <div class="card">
        <div class="card-head">
            <div class="card-head-icon"><i class="fas fa-list-check"></i></div>
            <h3>{{ $tab == 'harian' ? '30 Hari Terakhir' : 'Detail Bulan Ini' }}</h3>
        </div>
        <div style="overflow-x:auto;">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jam Masuk</th>
                        <th>Jam Pulang</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php $rows = $tab == 'harian' ? $harian : $bulanan; @endphp
                    @forelse($rows as $row)
                        <tr>
                            <td style="font-weight:600;">{{ $row->tanggal_display }}</td>
                            <td style="font-family:'JetBrains Mono',monospace; color:var(--text-muted);">{{ $row->waktu_datang_format }}</td>
                            <td style="font-family:'JetBrains Mono',monospace; color:var(--text-muted);">{{ $row->waktu_pulang_format }}</td>
                            <td>
                                @if($row->status == 'hadir')
                                    <span class="badge badge-hadir">Hadir</span>
                                @elseif($row->status == 'izin')
                                    <span class="badge badge-izin">Izin {{ $row->status_izin == 'pending' ? '• Menunggu' : '' }}</span>
                                @elseif($row->status == 'sakit')
                                    <span class="badge badge-sakit">Sakit {{ $row->status_izin == 'pending' ? '• Menunggu' : '' }}</span>
                                @elseif($row->status == 'cuti')
                                    <span class="badge badge-cuti">Cuti {{ $row->status_izin == 'pending' ? '• Menunggu' : '' }}</span>
                                @else
                                    <span class="badge badge-alpha">{{ ucfirst($row->status) }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" style="text-align:center; padding:3rem; color:var(--text-muted);">
                                <i class="fas fa-inbox" style="font-size:2rem; margin-bottom:0.75rem; display:block; opacity:0.3;"></i>
                                Belum ada data riwayat presensi
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</main>

{{-- MODALS PENGAJUAN --}}
<!-- Modal Izin -->
<div class="modal-overlay" id="modalIzin">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-file-alt" style="color:var(--warning); margin-right:8px;"></i>Pengajuan Izin</h3>
            <button class="modal-close" onclick="closeModal('izin')"><i class="fas fa-times"></i></button>
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
            <button class="btn-modal btn-cancel" onclick="closeModal('izin')">Batal</button>
            <button class="btn-modal btn-submit-izin" onclick="document.getElementById('formIzin').submit()">Kirim Izin</button>
        </div>
    </div>
</div>

<!-- Modal Sakit -->
<div class="modal-overlay" id="modalSakit">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-briefcase-medical" style="color:var(--danger); margin-right:8px;"></i>Pengajuan Sakit</h3>
            <button class="modal-close" onclick="closeModal('sakit')"><i class="fas fa-times"></i></button>
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
                    <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required style="padding:0.5rem;">
                </div>
                <div class="form-group">
                    <label class="form-label">Keterangan Penyakit</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Sakit demam, flu, dll..." required></textarea>
                </div>
            </form>
        </div>
        <div class="modal-footer">
            <button class="btn-modal btn-cancel" onclick="closeModal('sakit')">Batal</button>
            <button class="btn-modal btn-submit-sakit" onclick="document.getElementById('formSakit').submit()">Kirim Surat Sakit</button>
        </div>
    </div>
</div>

<!-- Modal Cuti -->
<div class="modal-overlay" id="modalCuti">
    <div class="modal-content">
        <div class="modal-header">
            <h3><i class="fas fa-calendar-days" style="color:var(--success); margin-right:8px;"></i>Pengajuan Cuti</h3>
            <button class="modal-close" onclick="closeModal('cuti')"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <form action="{{ route('presensi.cuti') }}" method="POST" id="formCuti">
                @csrf
                <input type="hidden" name="jenis" value="cuti">
                <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;">
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
            <button class="btn-modal btn-cancel" onclick="closeModal('cuti')">Batal</button>
            <button class="btn-modal btn-submit-cuti" onclick="document.getElementById('formCuti').submit()">Ajukan Cuti</button>
        </div>
    </div>
</div>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
function openModal(id) {
    const el = document.getElementById('modal' + id.charAt(0).toUpperCase() + id.slice(1));
    if(el) el.classList.add('show');
}
function closeModal(id) {
    const el = document.getElementById('modal' + id.charAt(0).toUpperCase() + id.slice(1));
    if(el) el.classList.remove('show');
}
setInterval(() => {
    const d = new Date();
    const t = d.toLocaleTimeString('id-ID', { hour12: false });
    const el = document.getElementById('liveClock');
    if(el) el.textContent = t;
}, 1000);
</script>
</body>
</html>
