<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Slip Gaji Saya — KOPPOSINDO</title>
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
            transition: transform 0.3s ease;
        }
        .sidebar-brand { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .brand-logo { width: 36px; height: 36px; background: #EA580C; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; flex-shrink: 0; }
        .brand-text h2 { font-size: 1rem; font-weight: 700; color: white; margin-bottom: 2px; }
        .brand-text p { font-size: 0.6rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; }

        .sidebar-menu { padding: 1.25rem 1rem; flex: 1; display: flex; flex-direction: column; gap: 2px; }
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

        /* MOBILE TOPBAR */
        .mobile-topbar { display: none; background: var(--primary); color: white; height: 60px; padding: 0 1.25rem; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 90; }
        .mobile-btn { background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; }

        /* MAIN */
        .main-content { margin-left: 260px; padding: 2rem; max-width: 1200px; }

        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .top-bar h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; }

        /* CARDS & SUMMARY */
        .summary-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1rem; margin-bottom: 2rem; }
        .summary-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 1.25rem; box-shadow: var(--shadow-sm);
        }
        .summary-card .lbl { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); letter-spacing: 0.05em; }
        .summary-card .val { font-size: 1.35rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; margin-top: 4px; color: var(--text-main); }
        .summary-card .sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 4px; }

        /* FILTER */
        .filter-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 1rem 1.5rem; margin-bottom: 1.5rem; box-shadow: var(--shadow-sm);
            display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 12px;
        }

        /* SLIP LIST */
        .slips-grid {
            display: grid; grid-template-columns: repeat(auto-fill, minmax(320px, 1fr)); gap: 1.25rem;
        }
        .slip-card-item {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 1.5rem; box-shadow: var(--shadow-sm); transition: transform 0.2s, box-shadow 0.2s;
            display: flex; flex-direction: column; justify-content: space-between;
        }
        .slip-card-item:hover {
            transform: translateY(-2px);
            box-shadow: var(--shadow-md);
            border-color: #cbd5e1;
        }
        .sc-head {
            display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1rem;
        }
        .sc-period { font-size: 1.15rem; font-weight: 800; color: var(--text-main); letter-spacing: -0.02em; }
        .sc-no { font-family: 'JetBrains Mono', monospace; font-size: 0.75rem; color: var(--text-muted); margin-top: 2px; }
        .sc-badge {
            background: var(--success-light); color: var(--success);
            padding: 0.25rem 0.6rem; border-radius: 6px; font-size: 0.7rem; font-weight: 700; text-transform: uppercase;
        }
        .sc-body {
            background: var(--bg-color); border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.25rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .sc-pay-label { font-size: 0.7rem; font-weight: 600; text-transform: uppercase; color: var(--text-muted); }
        .sc-pay-val { font-size: 1.25rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: var(--success); }
        
        .sc-stats {
            display: flex; justify-content: space-between; font-size: 0.75rem; color: var(--text-muted); margin-bottom: 1.25rem;
            border-top: 1px dashed var(--border); padding-top: 0.75rem;
        }
        .sc-actions {
            display: flex; gap: 8px;
        }
        .btn-view {
            flex: 1; background: var(--primary-light); color: var(--primary); text-align: center;
            padding: 0.6rem; border-radius: 8px; font-size: 0.825rem; font-weight: 600; text-decoration: none;
            transition: all 0.15s; display: inline-flex; align-items: center; justify-content: center; gap: 6px;
        }
        .btn-view:hover { background: var(--primary); color: white; }
        .btn-print-mini {
            background: var(--surface); color: var(--text-muted); border: 1px solid var(--border);
            padding: 0.6rem 0.85rem; border-radius: 8px; font-size: 0.825rem; font-weight: 600; text-decoration: none;
            transition: all 0.15s; display: inline-flex; align-items: center; justify-content: center;
        }
        .btn-print-mini:hover { border-color: var(--primary); color: var(--primary); }

        .empty-state {
            background: var(--surface); border: 1px dashed var(--border); border-radius: var(--radius-lg);
            padding: 3rem 1.5rem; text-align: center; color: var(--text-muted);
        }

        .overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 95; }

        @media (max-width: 900px) {
            .sidebar { transform: translateX(-100%); }
            .sidebar.active { transform: translateX(0); }
            .mobile-topbar { display: flex; }
            .main-content { margin-left: 0; padding: 1.25rem; }
            .overlay.active { display: block; }
        }
    </style>
</head>
<body>

    <div class="overlay" id="overlay" onclick="toggleSidebar()"></div>

    {{-- SIDEBAR KARYAWAN --}}
    <aside class="sidebar" id="sidebar">
        <div class="sidebar-brand">
            <div class="brand-logo"><i class="fas fa-users"></i></div>
            <div class="brand-text">
                <h2>KOPPOSINDO</h2>
                <p>Koperasi Pegawai Pos Indonesia</p>
            </div>
        </div>
        <div class="sidebar-menu">
            <a href="{{ route('presensi') }}" class="nav-item">
                <i class="fas fa-home"></i> Dashboard
            </a>
            <a href="{{ route('presensi.riwayat') }}" class="nav-item">
                <i class="fas fa-history"></i> Riwayat Presensi
            </a>
            <a href="{{ route('slip-gaji.index') }}" class="nav-item active">
                <i class="fas fa-file-invoice-dollar"></i> Slip Gaji
            </a>
            <a href="{{ route('profil') }}" class="nav-item">
                <i class="fas fa-user"></i> Profil
            </a>
            <form method="POST" action="{{ route('logout') }}" style="margin-top: auto;">
                @csrf
                <button type="submit" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
            </form>
        </div>
    </aside>

    {{-- MOBILE TOPBAR --}}
    <nav class="mobile-topbar">
        <button class="mobile-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
        <h1 style="font-size: 1.1rem; font-weight: 700;">Slip Gaji</h1>
        <div style="width: 24px;"></div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="main-content">
        <div class="top-bar">
            <div>
                <h1>Slip Gaji Saya</h1>
                <p style="font-size: 0.85rem; color: var(--text-muted); margin-top: 2px;">
                    Daftar penghasilan bulanan resmi yang telah dipublikasikan
                </p>
            </div>
        </div>

        {{-- SUMMARY --}}
        <div class="summary-grid">
            <div class="summary-card">
                <div class="lbl">Total Gaji Diterima</div>
                <div class="val" style="color: var(--success);">
                    {{ \App\Models\SlipGaji::rupiah($summary['total_diterima']) }}
                </div>
                <div class="sub">Akumulasi {{ $summary['jumlah_slip'] }} periode slip gaji</div>
            </div>
            <div class="summary-card">
                <div class="lbl">Rata-Rata Bulanan</div>
                <div class="val" style="color: var(--primary);">
                    {{ \App\Models\SlipGaji::rupiah($summary['rata_rata']) }}
                </div>
                <div class="sub">Per bulan aktif</div>
            </div>
            <div class="summary-card">
                <div class="lbl">Slip Terakhir</div>
                <div class="val" style="font-size: 1.15rem;">
                    {{ $summary['terakhir'] ? $summary['terakhir']->periode : 'Belum Ada' }}
                </div>
                <div class="sub">
                    {{ $summary['terakhir'] ? $summary['terakhir']->gaji_bersih_rupiah : '-' }}
                </div>
            </div>
        </div>

        {{-- FILTER TAHUN --}}
        <div class="filter-card">
            <form method="GET" action="{{ route('slip-gaji.index') }}" style="display:flex; align-items:center; gap:10px; width:100%; flex-wrap:wrap;">
                <label for="tahun" style="font-size: 0.825rem; font-weight:600; color:var(--text-muted);">Tahun Periode:</label>
                <select name="tahun" id="tahun" onchange="this.form.submit()" style="padding: 0.5rem 0.75rem; border: 1px solid var(--border); border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                    <option value="">Semua Tahun</option>
                    @foreach ($daftarTahun as $t)
                        <option value="{{ $t }}" {{ $tahun == $t ? 'selected' : '' }}>{{ $t }}</option>
                    @endforeach
                </select>
                @if ($tahun)
                    <a href="{{ route('slip-gaji.index') }}" style="font-size: 0.8rem; color: var(--primary); text-decoration: none;">Reset Filter</a>
                @endif
            </form>
        </div>

        {{-- DAFTAR SLIP GAJI --}}
        @if ($slips->isEmpty())
            <div class="empty-state">
                <i class="fas fa-file-invoice" style="font-size: 2.5rem; opacity: 0.3; margin-bottom: 1rem; display: block;"></i>
                <h3 style="font-size: 1rem; font-weight: 700; color: var(--text-main); margin-bottom: 4px;">Belum Ada Slip Gaji</h3>
                <p style="font-size: 0.85rem;">Slip gaji Anda akan muncul di sini setelah diterbitkan oleh bagian HRD / Keuangan.</p>
            </div>
        @else
            <div class="slips-grid">
                @foreach ($slips as $s)
                    <div class="slip-card-item">
                        <div>
                            <div class="sc-head">
                                <div>
                                    <div class="sc-period">{{ $s->periode }}</div>
                                    <div class="sc-no">{{ $s->nomor_slip }}</div>
                                </div>
                                <span class="sc-badge"><i class="fas fa-check-circle"></i> Published</span>
                            </div>

                            <div class="sc-body">
                                <div>
                                    <div class="sc-pay-label">Take Home Pay</div>
                                    <div class="sc-pay-val">{{ $s->gaji_bersih_rupiah }}</div>
                                </div>
                                <i class="fas fa-wallet" style="font-size: 1.5rem; color: var(--success); opacity: 0.5;"></i>
                            </div>

                            <div class="sc-stats">
                                <span><i class="fas fa-calendar-check" style="color:var(--success);"></i> Hadir: <strong>{{ $s->total_hadir }}</strong> hr</span>
                                <span><i class="fas fa-clock" style="color:var(--warning);"></i> Izin/Sakit: <strong>{{ $s->total_izin + $s->total_sakit }}</strong> hr</span>
                                <span><i class="fas fa-umbrella-beach" style="color:#8b5cf6;"></i> Cuti: <strong>{{ $s->total_cuti }}</strong> hr</span>
                            </div>
                        </div>

                        <div class="sc-actions">
                            <a href="{{ route('slip-gaji.show', $s->id) }}" class="btn-view">
                                <i class="fas fa-eye"></i> Rincian Slip
                            </a>
                            <a href="{{ route('slip-gaji.cetak', $s->id) }}" target="_blank" class="btn-print-mini" title="Cetak / Simpan PDF">
                                <i class="fas fa-print"></i>
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>
</html>
