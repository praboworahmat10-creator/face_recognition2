<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Detail Slip Gaji — {{ $slipGaji->periode }}</title>
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
        .main-content { margin-left: 260px; padding: 2rem; max-width: 960px; }

        .action-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; flex-wrap: wrap; gap: 12px; }
        .btn-back {
            background: var(--surface); color: var(--text-main); border: 1px solid var(--border);
            padding: 0.6rem 1rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;
            display: inline-flex; align-items: center; gap: 6px;
        }
        .btn-print-main {
            background: var(--primary); color: white; border: none;
            padding: 0.65rem 1.25rem; border-radius: 8px; font-size: 0.85rem; font-weight: 600; text-decoration: none;
            display: inline-flex; align-items: center; gap: 8px; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.3);
        }
        .btn-print-main:hover { background: var(--primary-hover); }

        .paper-card {
            background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg);
            padding: 2.25rem; box-shadow: var(--shadow-sm);
        }
        .paper-header {
            display: flex; justify-content: space-between; align-items: flex-start;
            padding-bottom: 1.5rem; border-bottom: 2px solid var(--border); margin-bottom: 1.75rem;
        }
        .paper-title h2 { font-size: 1.35rem; font-weight: 800; color: var(--text-main); }
        .paper-title p { font-size: 0.8rem; color: var(--text-muted); margin-top: 2px; }

        .k-box {
            display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 1rem;
            background: var(--bg-color); border: 1px solid var(--border); border-radius: var(--radius-md);
            padding: 1.25rem; margin-bottom: 1.75rem;
        }
        .k-item .lbl { font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; }
        .k-item .val { font-size: 0.95rem; font-weight: 700; color: var(--text-main); margin-top: 3px; }

        .p-strip {
            display: flex; justify-content: space-around; background: var(--surface); border: 1px solid var(--border);
            border-radius: var(--radius-md); padding: 0.85rem 1rem; margin-bottom: 1.75rem; text-align: center;
        }
        .p-strip span { font-size: 0.7rem; text-transform: uppercase; color: var(--text-muted); font-weight: 600; display: block; }
        .p-strip strong { font-family: 'JetBrains Mono', monospace; font-size: 1.1rem; }

        .tables-split { display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem; margin-bottom: 1.75rem; }
        @media (max-width: 768px) { .tables-split { grid-template-columns: 1fr; } }

        .sec-tbl { width: 100%; border-collapse: collapse; font-size: 0.85rem; border: 1px solid var(--border); border-radius: 8px; overflow: hidden; }
        .sec-tbl th { background: var(--bg-color); padding: 0.75rem 1rem; text-align: left; font-size: 0.75rem; text-transform: uppercase; font-weight: 700; border-bottom: 1px solid var(--border); }
        .sec-tbl td { padding: 0.75rem 1rem; border-bottom: 1px solid var(--border); }
        .sec-tbl tr:last-child td { border-bottom: none; }
        .sec-tbl .amt { text-align: right; font-family: 'JetBrains Mono', monospace; font-weight: 600; }

        .take-home-box {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%); color: white;
            padding: 1.5rem 1.75rem; border-radius: var(--radius-md); margin-bottom: 1.5rem;
            display: flex; justify-content: space-between; align-items: center;
        }
        .th-val { font-size: 1.75rem; font-weight: 800; font-family: 'JetBrains Mono', monospace; color: #34d399; }

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
        <h1 style="font-size: 1.1rem; font-weight: 700;">Rincian Slip Gaji</h1>
        <div style="width: 24px;"></div>
    </nav>

    {{-- MAIN CONTENT --}}
    <main class="main-content">
        <div class="action-bar">
            <a href="{{ route('slip-gaji.index') }}" class="btn-back">
                <i class="fas fa-arrow-left"></i> Kembali ke Daftar
            </a>
            <a href="{{ route('slip-gaji.cetak', $slipGaji->id) }}" target="_blank" class="btn-print-main">
                <i class="fas fa-print"></i> Cetak / Simpan PDF
            </a>
        </div>

        <div class="paper-card">
            <div class="paper-header">
                <div class="paper-title">
                    <h2>KOPPOSINDO</h2>
                    <p>Koperasi Pegawai Pos Indonesia</p>
                </div>
                <div style="text-align: right;">
                    <div style="font-size: 1rem; font-weight: 800; color: var(--primary);">SLIP GAJI</div>
                    <div style="font-family:'JetBrains Mono'; font-size:0.75rem; color:var(--text-muted);">{{ $slipGaji->nomor_slip }}</div>
                    <div style="margin-top: 4px;">
                        <span style="background:var(--success-light); color:var(--success); font-size:0.65rem; font-weight:700; padding:2px 8px; border-radius:4px; text-transform:uppercase;">
                            Published
                        </span>
                    </div>
                </div>
            </div>

            {{-- KARYAWAN INFO --}}
            <div class="k-box">
                <div class="k-item">
                    <div class="lbl">Nama Karyawan</div>
                    <div class="val">{{ $slipGaji->karyawan->nama }}</div>
                </div>
                <div class="k-item">
                    <div class="lbl">NIK</div>
                    <div class="val" style="font-family:'JetBrains Mono';">{{ $slipGaji->karyawan->nik }}</div>
                </div>
                <div class="k-item">
                    <div class="lbl">Jabatan / Divisi</div>
                    <div class="val">{{ $slipGaji->karyawan->jabatan ?? '-' }} / {{ $slipGaji->karyawan->divisi ?? '-' }}</div>
                </div>
                <div class="k-item">
                    <div class="lbl">Periode Gaji</div>
                    <div class="val" style="color:var(--primary);">{{ $slipGaji->periode }}</div>
                </div>
            </div>

            {{-- REKAP PRESENSI --}}
            <div class="p-strip">
                <div>
                    <span>Hadir</span>
                    <strong style="color:var(--success);">{{ $slipGaji->total_hadir }} hr</strong>
                </div>
                <div>
                    <span>Izin</span>
                    <strong style="color:var(--warning);">{{ $slipGaji->total_izin }} hr</strong>
                </div>
                <div>
                    <span>Sakit</span>
                    <strong style="color:#0ea5e9;">{{ $slipGaji->total_sakit }} hr</strong>
                </div>
                <div>
                    <span>Cuti</span>
                    <strong style="color:#8b5cf6;">{{ $slipGaji->total_cuti }} hr</strong>
                </div>
                <div>
                    <span>Alpa</span>
                    <strong style="color:var(--danger);">{{ $slipGaji->total_alpa }} hr</strong>
                </div>
            </div>

            {{-- TABEL --}}
            <div class="tables-split">
                <div>
                    <table class="sec-tbl">
                        <thead>
                            <tr>
                                <th style="color:var(--success);">A. Pendapatan</th>
                                <th style="text-align:right;">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>Gaji Pokok</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->gaji_pokok) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Jabatan</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_jabatan) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Kehadiran</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_kehadiran) }}</td>
                            </tr>
                            <tr>
                                <td>Tunjangan Transport & Makan</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->tunjangan_transport_makan) }}</td>
                            </tr>
                            <tr>
                                <td>Bonus & Insentif</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->bonus) }}</td>
                            </tr>
                            <tr style="background:var(--bg-color); font-weight:700;">
                                <td>Total Penerimaan (A)</td>
                                <td class="amt" style="color:var(--success);">{{ \App\Models\SlipGaji::rupiah($slipGaji->total_pendapatan) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div>
                    <table class="sec-tbl">
                        <thead>
                            <tr>
                                <th style="color:var(--danger);">B. Potongan</th>
                                <th style="text-align:right;">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>BPJS Kesehatan / TK</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_bpjs) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Terlambat</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_terlambat) }}</td>
                            </tr>
                            <tr>
                                <td>Potongan Lainnya / Alpa</td>
                                <td class="amt">{{ \App\Models\SlipGaji::rupiah($slipGaji->potongan_lainnya) }}</td>
                            </tr>
                            <tr style="background:var(--bg-color); font-weight:700;">
                                <td>Total Potongan (B)</td>
                                <td class="amt" style="color:var(--danger);">{{ \App\Models\SlipGaji::rupiah($slipGaji->total_potongan) }}</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- TAKE HOME PAY --}}
            <div class="take-home-box">
                <div>
                    <div style="font-size: 0.75rem; text-transform:uppercase; letter-spacing:0.05em; opacity:0.8;">Gaji Bersih Diterima (Take Home Pay)</div>
                    <div style="font-size: 0.8rem; font-style:italic; opacity:0.75; margin-top:2px;"># {{ ucwords(\App\Models\SlipGaji::angkaKeHuruf($slipGaji->gaji_bersih)) }} Rupiah #</div>
                </div>
                <div class="th-val">{{ $slipGaji->gaji_bersih_rupiah }}</div>
            </div>

            @if ($slipGaji->catatan)
                <div style="font-size: 0.8rem; color:var(--text-muted); font-style:italic; border-left:3px solid var(--border); padding-left:10px;">
                    Catatan: {{ $slipGaji->catatan }}
                </div>
            @endif
        </div>
    </main>

    <script>
        function toggleSidebar() {
            document.getElementById('sidebar').classList.toggle('active');
            document.getElementById('overlay').classList.toggle('active');
        }
    </script>
</body>
</html>
