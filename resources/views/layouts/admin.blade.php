<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Admin Panel') — FaceID Presensi</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <style>
        :root {
            /* Warna Dasar Premium Light Mode */
            --bg: #F8FAFC;          /* Slate 50 */
            --surface: #FFFFFF;     /* White */
            --border: #E2E8F0;      /* Slate 200 */
            
            /* Tipografi */
            --text-main: #0F172A;   /* Slate 900 */
            --text-muted: #64748B;  /* Slate 500 */
            --text-light: #94A3B8;  /* Slate 400 */
            
            /* Warna Brand / Aksen */
            --primary: #4F46E5;         /* Indigo 600 */
            --primary-hover: #4338CA;   /* Indigo 700 */
            --primary-light: #EEF2FF;   /* Indigo 50 */
            
            /* Warna Status */
            --success: #10B981;         /* Emerald 500 */
            --success-light: #D1FAE5;   /* Emerald 100 */
            --success-dark: #065F46;    /* Emerald 800 */
            
            --warning: #F59E0B;         /* Amber 500 */
            --warning-light: #FEF3C7;   /* Amber 100 */
            --warning-dark: #92400E;    /* Amber 800 */
            
            --danger: #EF4444;          /* Red 500 */
            --danger-light: #FEE2E2;    /* Red 100 */
            --danger-dark: #991B1B;     /* Red 800 */
            
            /* Efek Visual */
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05), 0 2px 4px -2px rgb(0 0 0 / 0.05);
            --radius-md: 12px;
            --radius-lg: 16px;
        }

        * { margin:0; padding:0; box-sizing:border-box; }
        body { 
            font-family: 'Inter', sans-serif; 
            background: var(--bg); 
            color: var(--text-main); 
            min-height: 100vh; 
            display: flex;
            -webkit-font-smoothing: antialiased;
        }

        .mono { font-family: 'JetBrains Mono', monospace; }

        /* ── SIDEBAR ── */
        .sidebar {
            width: 260px;
            background: var(--surface);
            border-right: 1px solid var(--border);
            padding: 1.5rem;
            display: flex;
            flex-direction: column;
            position: fixed;
            height: 100vh;
            z-index: 100;
        }
        .sidebar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            padding-bottom: 1.5rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border);
        }
        .brand-logo {
            width: 36px; height: 36px;
            background: var(--primary);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 700; font-size: 14px; color: #fff;
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.3);
        }
        .brand-name { font-size: 1rem; font-weight: 700; color: var(--text-main); line-height: 1.2; letter-spacing: -0.02em; }
        .brand-sub { font-size: 0.65rem; color: var(--text-muted); font-weight: 500; letter-spacing: 0.05em; text-transform: uppercase; }
        
        .sidebar-nav { flex: 1; overflow-y: auto; }
        .nav-section { margin-bottom: 1.5rem; }
        .nav-label { 
            font-size: 0.65rem; 
            font-weight: 600; 
            letter-spacing: 0.05em; 
            color: var(--text-light); 
            text-transform: uppercase; 
            margin-bottom: 0.75rem; 
            padding-left: 0.5rem;
        }
        .nav-item {
            display: flex; align-items: center; gap: 12px;
            padding: 0.6rem 0.8rem; 
            border-radius: 8px;
            font-size: 0.85rem; font-weight: 500;
            color: var(--text-muted); cursor: pointer;
            transition: all 0.2s ease; 
            margin-bottom: 4px;
            text-decoration: none;
        }
        .nav-item:hover { background: var(--bg); color: var(--text-main); }
        .nav-item.active { background: var(--primary-light); color: var(--primary); font-weight: 600; }
        .nav-item i { width: 16px; text-align: center; font-size: 0.9rem; }
        
        .sidebar-footer { margin-top: auto; padding-top: 1.5rem; border-top: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .admin-info { display: flex; align-items: center; gap: 12px; }
        .admin-avatar {
            width: 36px; height: 36px; border-radius: 50%;
            background: var(--bg); border: 1px solid var(--border);
            display: flex; align-items: center; justify-content: center;
            font-size: 0.85rem; font-weight: 600; color: var(--primary);
        }
        .admin-name { font-size: 0.85rem; font-weight: 600; color: var(--text-main); }
        .admin-role { font-size: 0.7rem; color: var(--text-muted); }

        /* ── MAIN CONTENT ── */
        .main { margin-left: 260px; flex: 1; display: flex; flex-direction: column; min-height: 100vh; }
        
        .topbar {
            background: var(--surface);
            border-bottom: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
            padding: 1.2rem 2.5rem;
            position: sticky; top: 0; z-index: 50;
            box-shadow: 0 1px 2px 0 rgb(0 0 0 / 0.02);
        }
        .page-title h1 { font-size: 1.4rem; font-weight: 700; letter-spacing: -0.02em; color: var(--text-main); }
        
        .topbar-actions { display: flex; gap: 12px; align-items: center; }
        .time-badge {
            font-size: 0.8rem; font-weight: 500; color: var(--text-muted);
            background: var(--bg); padding: 0.5rem 1rem;
            border-radius: 8px; border: 1px solid var(--border);
            font-family: 'JetBrains Mono', monospace;
        }

        .page-content { padding: 2rem 2.5rem; max-width: 1400px; width: 100%; margin: 0 auto; flex: 1; }

        /* Flash Messages */
        .flash-msg { margin: 1.5rem 2.5rem 0; padding: 1rem 1.25rem; border-radius: 10px; font-size: 0.85rem; font-weight: 500; display: flex; align-items: center; gap: 10px; }
        .flash-success { background: var(--success-light); color: var(--success-dark); border: 1px solid #A7F3D0; }
        .flash-error { background: var(--danger-light); color: var(--danger-dark); border: 1px solid #FECACA; }

        /* ── SHARED COMPONENTS (FOR OTHER PAGES) ── */
        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 1.5rem; box-shadow: var(--shadow-sm); margin-bottom: 1.5rem; }
        .card-header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; border-bottom: 1px solid var(--border); padding-bottom: 1rem; }
        .card-title { font-size: 1.1rem; font-weight: 600; color: var(--text-main); letter-spacing: -0.01em; }

        .btn {
            display: inline-flex; align-items: center; justify-content: center; gap: 8px;
            padding: 0.6rem 1.2rem; border-radius: 8px;
            font-family: inherit; font-size: 0.85rem; font-weight: 500;
            border: none; cursor: pointer; transition: all 0.2s; text-decoration: none;
        }
        .btn-primary { background: var(--primary); color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.05); }
        .btn-primary:hover { background: var(--primary-hover); }
        .btn-secondary { background: var(--bg); color: var(--text-main); border: 1px solid var(--border); }
        .btn-secondary:hover { border-color: #CBD5E1; background: #F1F5F9; }
        .btn-danger { background: var(--danger-light); color: var(--danger-dark); }
        .btn-danger:hover { background: #FECACA; }
        .btn-sm { padding: 0.4rem 0.8rem; font-size: 0.8rem; }

        .badge { display: inline-flex; align-items: center; gap: 4px; padding: 0.35rem 0.75rem; border-radius: 20px; font-size: 0.75rem; font-weight: 600; }
        .badge-hadir { background: var(--success-light); color: var(--success-dark); }
        .badge-izin { background: var(--warning-light); color: var(--warning-dark); }
        .badge-sakit { background: var(--warning-light); color: var(--warning-dark); }
        .badge-cuti { background: var(--primary-light); color: var(--primary-hover); }
        .badge-alpha { background: var(--danger-light); color: var(--danger-dark); }
        
        .badge-primary { background: var(--primary-light); color: var(--primary-hover); }
        .badge-danger { background: var(--danger-light); color: var(--danger-dark); }
        .badge-warning { background: var(--warning-light); color: var(--warning-dark); }
        .badge-success { background: var(--success-light); color: var(--success-dark); }

        table { width: 100%; border-collapse: collapse; text-align: left; }
        th { padding: 1rem 1.5rem; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.05em; background: var(--bg); border-bottom: 1px solid var(--border); }
        td { padding: 1rem 1.5rem; font-size: 0.9rem; border-bottom: 1px solid var(--border); color: var(--text-main); vertical-align: middle; }
        tr:last-child td { border-bottom: none; }
        tbody tr:hover { background: var(--bg); }
        .table-responsive { overflow-x: auto; }

        /* Form Inputs */
        input.form-control, select.form-control, textarea.form-control {
            width: 100%; padding: 0.6rem 1rem; font-size: 0.9rem; font-family: inherit; color: var(--text-main);
            background: var(--surface); border: 1px solid var(--border); border-radius: 8px; outline: none; transition: all 0.2s;
        }
        input.form-control:focus, select.form-control:focus, textarea.form-control:focus {
            border-color: var(--primary); box-shadow: 0 0 0 3px var(--primary-light);
        }
        label { display: block; font-size: 0.85rem; font-weight: 500; color: var(--text-main); margin-bottom: 0.5rem; }
        .form-group { margin-bottom: 1.25rem; }

        /* Modal Overlay */
        .modal-backdrop { position: fixed; inset: 0; background: rgba(15, 23, 42, 0.5); backdrop-filter: blur(4px); display: flex; align-items: center; justify-content: center; z-index: 9999; animation: fadeIn 0.2s ease-out; }
        .modal-content { background: var(--surface); border-radius: var(--radius-lg); width: 90%; max-width: 500px; box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1); overflow: hidden; animation: slideUp 0.2s ease-out; }
        .modal-header { padding: 1.25rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; justify-content: space-between; }
        .modal-header h3 { font-size: 1.1rem; font-weight: 600; color: var(--text-main); }
        .modal-close { background: none; border: none; font-size: 1.5rem; color: var(--text-muted); cursor: pointer; transition: color 0.2s; }
        .modal-close:hover { color: var(--text-main); }
        .modal-body { padding: 1.5rem; }

        @keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
        @keyframes slideUp { from { transform: translateY(20px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

        /* Laravel Pagination overrides */
        .pagination-wrap nav { display: flex; align-items: center; justify-content: space-between; }
        .pagination-wrap svg { width: 1rem; height: 1rem; }
        .pagination-wrap a, .pagination-wrap span.cursor-default { 
            padding: 0.5rem 0.75rem; margin: 0 0.1rem; border: 1px solid var(--border); border-radius: 6px; 
            background: var(--surface); color: var(--text-muted); font-size: 0.85rem; text-decoration: none;
        }
        .pagination-wrap span[aria-current="page"] > span { background: var(--primary); color: white; border-color: var(--primary); }
        .pagination-wrap a:hover { background: var(--bg); color: var(--text-main); }
    </style>
    @stack('styles')
</head>
<body>

<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo">F</div>
        <div>
            <div class="brand-name">FaceID</div>
            <div class="brand-sub">Admin Dashboard</div>
        </div>
    </div>

    <nav class="sidebar-nav">
        <div class="nav-section">
            <div class="nav-label">Main Menu</div>
            <a href="{{ route('admin.dashboard') }}" class="nav-item {{ request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                <i class="fa fa-chart-pie"></i> Dashboard
            </a>
            <a href="{{ route('admin.presensi.rekap') }}" class="nav-item {{ request()->routeIs('admin.presensi.rekap') || request()->routeIs('admin.presensi.rekap-bulanan') ? 'active' : '' }}">
                <i class="fa fa-list"></i> Rekap Presensi
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Manajemen</div>
            <a href="{{ route('admin.karyawan.index') }}" class="nav-item {{ request()->routeIs('admin.karyawan.*') && !request()->routeIs('admin.karyawan.enroll-face.new') ? 'active' : '' }}">
                <i class="fa fa-users"></i> Data Karyawan
            </a>
            <a href="{{ route('admin.karyawan.enroll-face.new') }}" class="nav-item {{ request()->routeIs('admin.karyawan.enroll-face.new') ? 'active' : '' }}">
                <i class="fa fa-camera"></i> Enroll Wajah
            </a>
            <a href="{{ route('admin.izin') }}" class="nav-item {{ request()->routeIs('admin.izin') ? 'active' : '' }}">
                <i class="fa fa-calendar-check"></i> Izin & Cuti
            </a>
            <a href="{{ route('admin.slip-gaji.index') }}" class="nav-item {{ request()->routeIs('admin.slip-gaji.*') ? 'active' : '' }}">
                <i class="fa fa-file-invoice-dollar"></i> Slip Gaji
            </a>
        </div>

        <div class="nav-section">
            <div class="nav-label">Laporan</div>
            <a href="{{ route('admin.presensi.export') }}" class="nav-item">
                <i class="fa fa-download"></i> Export Harian (CSV)
            </a>
            <a href="{{ route('admin.presensi.export-bulanan') }}" class="nav-item">
                <i class="fa fa-file-excel"></i> Export Bulanan (CSV)
            </a>
        </div>
    </nav>

    <div class="sidebar-footer">
        <div class="admin-info">
            <div class="admin-avatar">{{ strtoupper(substr(Auth::user()->name ?? 'A', 0, 1)) }}</div>
            <div>
                <div class="admin-name">{{ Auth::user()->name ?? 'Administrator' }}</div>
                <div class="admin-role">Super Admin</div>
            </div>
        </div>
        <form action="{{ route('logout') }}" method="POST">
            @csrf
            <button type="submit" class="btn btn-danger btn-sm" title="Logout" style="padding: 0.4rem 0.6rem; border-radius: 6px;"><i class="fa fa-sign-out-alt"></i></button>
        </form>
    </div>
</aside>

<div class="main">
    <header class="topbar">
        <div class="page-title">
            <h1>@yield('page-title', 'Admin Panel')</h1>
        </div>
        <div class="topbar-actions">
            <div class="time-badge" id="clock"><i class="far fa-clock" style="margin-right:6px; color:var(--text-light);"></i> --:--:--</div>
        </div>
    </header>

    @if(session('success'))
    <div class="flash-msg flash-success">
        <i class="fa fa-check-circle"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="flash-msg flash-error">
        <i class="fa fa-exclamation-circle"></i> {{ session('error') }}
    </div>
    @endif

    <main class="page-content">
        @yield('content')
    </main>
</div>

<!-- Global Modal Logic from previous layout -->
<div id="detailModal" class="modal-backdrop" style="display:none;" onclick="event.target === this && closeDetailModal()">
    <div class="modal-content">
        <div class="modal-header">
            <h3>Detail Data</h3>
            <button class="modal-close" onclick="closeDetailModal()">&times;</button>
        </div>
        <div class="modal-body" id="modalBodyContent">
            <!-- Injected by scripts -->
        </div>
    </div>
</div>

<script>
    function updateClock() {
        const now = new Date();
        const timeStr = [now.getHours(), now.getMinutes(), now.getSeconds()]
            .map(n => String(n).padStart(2,'0')).join(':');
        document.getElementById('clock').innerHTML = `<i class="far fa-clock" style="margin-right:6px; color:var(--text-light);"></i> ${timeStr}`;
    }
    setInterval(updateClock, 1000);
    updateClock();

    function closeDetailModal() {
        document.getElementById('detailModal').style.display = 'none';
    }
</script>
@stack('scripts')
</body>
</html>