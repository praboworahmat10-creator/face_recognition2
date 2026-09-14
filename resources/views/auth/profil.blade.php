<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Saya — KOPPOSINDO</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --bg-color: #F8FAFC; --surface: #FFFFFF; --border: #E2E8F0;
            --text-main: #1E293B; --text-muted: #64748B;
            --primary: #3B82F6; --primary-light: #EFF6FF;
            --sidebar-bg: #0F172A;
            --success: #10B981; --success-light: #D1FAE5;
            --warning: #F59E0B; --warning-light: #FEF3C7;
            --danger: #EF4444; --danger-light: #FEE2E2;
            --shadow-sm: 0 1px 2px 0 rgb(0 0 0 / 0.05);
            --shadow-md: 0 4px 6px -1px rgb(0 0 0 / 0.05);
            --radius-lg: 16px;
        }
        * { margin:0; padding:0; box-sizing:border-box; }
        body { font-family: 'Inter', sans-serif; background: var(--bg-color); color: var(--text-main); min-height: 100vh; -webkit-font-smoothing: antialiased; }

        .sidebar { position: fixed; top: 0; left: 0; bottom: 0; width: 260px; background: var(--sidebar-bg); color: white; z-index: 100; display: flex; flex-direction: column; overflow-y: auto; }
        .sidebar-brand { padding: 1.5rem; display: flex; align-items: center; gap: 12px; border-bottom: 1px solid rgba(255,255,255,0.08); }
        .brand-logo { width: 36px; height: 36px; background: #EA580C; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 16px; flex-shrink: 0; }
        .brand-text h2 { font-size: 1rem; font-weight: 700; color: white; margin-bottom: 2px; }
        .brand-text p { font-size: 0.6rem; color: #94A3B8; text-transform: uppercase; letter-spacing: 0.05em; }
        .sidebar-menu { padding: 1.25rem 1rem; flex: 1; display: flex; flex-direction: column; gap: 2px; }
        .nav-label { font-size: 0.65rem; font-weight: 700; color: rgba(148,163,184,0.5); text-transform: uppercase; letter-spacing: 0.08em; padding: 0 0.5rem; margin: 1rem 0 0.4rem; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 0.75rem 0.875rem; color: #94A3B8; text-decoration: none; font-weight: 500; font-size: 0.875rem; border-radius: 8px; transition: all 0.15s; border: none; background: none; width: 100%; cursor: pointer; text-align: left; }
        .nav-item i { width: 18px; text-align: center; font-size: 1rem; flex-shrink: 0; }
        .nav-item:hover { background: rgba(255,255,255,0.07); color: #e2e8f0; }
        .nav-item.active { background: var(--primary); color: white; font-weight: 600; }
        .nav-item.logout { color: #EF4444; }
        .nav-item.logout:hover { background: rgba(239,68,68,0.1); }
        .sidebar-footer { padding: 1rem; border-top: 1px solid rgba(255,255,255,0.07); }

        .mobile-topbar { display: none; background: var(--primary); color: white; height: 60px; padding: 0 1.25rem; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 90; }
        .mobile-btn { background: none; border: none; color: white; font-size: 1.2rem; cursor: pointer; }
        .sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 99; }
        .sidebar-overlay.active { display: block; }
        .sidebar.active { transform: translateX(0) !important; }

        .main-content { margin-left: 260px; padding: 2rem; max-width: 900px; }
        .top-bar { display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem; }
        .top-bar h1 { font-size: 1.5rem; font-weight: 800; letter-spacing: -0.03em; }
        .top-bar p { font-size: 0.85rem; color: var(--text-muted); margin-top: 2px; }
        .time-pill { font-family: 'JetBrains Mono', monospace; font-size: 0.9rem; font-weight: 700; color: var(--primary); background: var(--primary-light); padding: 0.5rem 1rem; border-radius: 10px; border: 1px solid rgba(59,130,246,0.15); }

        .profile-hero { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); padding: 2rem; margin-bottom: 1.5rem; display: flex; align-items: center; gap: 20px; box-shadow: var(--shadow-sm); position: relative; overflow: hidden; }
        .profile-hero::before { content: ''; position: absolute; top: 0; left: 0; right: 0; height: 5px; background: linear-gradient(90deg, #3B82F6, #10B981); }
        .profile-avatar { width: 80px; height: 80px; border-radius: 20px; background: var(--primary-light); color: var(--primary); display: flex; align-items: center; justify-content: center; font-size: 2rem; font-weight: 700; overflow: hidden; border: 3px solid white; box-shadow: var(--shadow-md); flex-shrink: 0; }
        .profile-avatar img { width: 100%; height: 100%; object-fit: cover; }
        .profile-info h2 { font-size: 1.4rem; font-weight: 800; margin-bottom: 4px; }
        .profile-info p { color: var(--text-muted); font-size: 0.9rem; }

        .card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); margin-bottom: 1.5rem; box-shadow: var(--shadow-sm); overflow: hidden; }
        .card-head { padding: 1rem 1.5rem; border-bottom: 1px solid var(--border); display: flex; align-items: center; gap: 10px; background: #FAFAFA; }
        .card-head-icon { width: 30px; height: 30px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 0.85rem; }
        .card-body { padding: 1.5rem; }
        .info-row { display: flex; justify-content: space-between; align-items: center; padding: 0.875rem 0; border-bottom: 1px dashed var(--border); font-size: 0.9rem; }
        .info-row:last-child { border-bottom: none; }
        .info-lbl { color: var(--text-muted); font-weight: 500; }
        .info-val { font-weight: 700; }
        .nik-badge { font-family: 'JetBrains Mono', monospace; background: var(--primary-light); color: var(--primary); padding: 3px 10px; border-radius: 6px; font-size: 0.8rem; font-weight: 700; }

        .alert { padding: 1rem; border-radius: 10px; margin-bottom: 1rem; display: flex; align-items: center; gap: 10px; font-size: 0.875rem; font-weight: 600; }
        .alert-success { background: var(--success-light); color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: var(--danger-light); color: #9f1239; border: 1px solid #fecdd3; }
        .form-group { margin-bottom: 1.25rem; }
        .form-label { display: block; font-size: 0.825rem; font-weight: 700; margin-bottom: 0.5rem; color: var(--text-main); }
        .input-wrap { position: relative; }
        .input-icon { position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.875rem; }
        .form-input { width: 100%; padding: 0.8rem 1rem 0.8rem 2.5rem; border: 1.5px solid var(--border); border-radius: 10px; font-family: 'Inter', sans-serif; font-size: 0.9rem; background: var(--bg-color); outline: none; transition: all 0.2s; }
        .form-input:focus { border-color: var(--primary); background: var(--surface); box-shadow: 0 0 0 3px var(--primary-light); }
        .toggle-pw { position: absolute; right: 12px; top: 50%; transform: translateY(-50%); background: none; border: none; color: var(--text-muted); cursor: pointer; }
        .invalid-feedback { font-size: 0.8rem; color: var(--danger); margin-top: 4px; display: flex; align-items: center; gap: 4px; }
        .btn-save { padding: 0.875rem 1.5rem; background: var(--primary); color: white; border: none; border-radius: 10px; font-weight: 700; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; transition: all 0.15s; }
        .btn-save:hover { background: var(--primary-hover); }

        @media (max-width: 768px) {
            .sidebar { transform: translateX(-100%); transition: transform 0.3s; }
            .mobile-topbar { display: flex; }
            .main-content { margin-left: 0; padding: 1rem; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay" onclick="toggleSidebar()"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="brand-logo"><i class="fas fa-users" style="color:white;"></i></div>
        <div class="brand-text"><h2>KOPPOSINDO</h2><p>Koperasi Pegawai Pos Indonesia</p></div>
    </div>
    <div class="sidebar-menu">
        <div class="nav-label">Menu Utama</div>
        <a href="{{ route('presensi') }}" class="nav-item"><i class="fas fa-home"></i> Dashboard</a>
        <a href="{{ route('presensi') }}" class="nav-item"><i class="fas fa-fingerprint"></i> Presensi</a>
        <a href="{{ route('presensi.riwayat') }}" class="nav-item"><i class="fas fa-history"></i> Riwayat Presensi</a>
        <a href="{{ route('profil') }}" class="nav-item active"><i class="fas fa-user"></i> Profil</a>
    </div>
    <div class="sidebar-footer">
        <form method="POST" action="{{ route('logout') }}">
            @csrf
            <button type="submit" class="nav-item logout"><i class="fas fa-sign-out-alt"></i> Logout</button>
        </form>
    </div>
</aside>

<nav class="mobile-topbar">
    <button class="mobile-btn" onclick="toggleSidebar()"><i class="fas fa-bars"></i></button>
    <span style="font-weight:700;">Profil Saya</span>
    <span></span>
</nav>

<main class="main-content">
    <div class="top-bar">
        <div>
            <h1>Profil Saya</h1>
            <p>Lihat data dan kelola password akun kamu</p>
        </div>
        <div class="time-pill" id="liveClock">00:00:00</div>
    </div>

    @if($karyawan)
    <div class="profile-hero">
        @if($karyawan->foto)
            <img src="{{ asset('storage/' . $karyawan->foto) }}" alt="Foto Profil" class="profile-avatar">
        @else
            <div class="profile-avatar"><i class="fas fa-user"></i></div>
        @endif
        <div class="profile-info">
            <h2>{{ $karyawan->nama }}</h2>
            <p>{{ $karyawan->jabatan }} &bull; {{ $karyawan->divisi }}</p>
        </div>
    </div>
    @endif

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon" style="background:var(--primary-light);color:var(--primary);"><i class="fas fa-id-card"></i></div>
            <h3 style="font-size:0.95rem;font-weight:700;">Data Karyawan</h3>
        </div>
        <div class="card-body">
            @if($karyawan)
                <div class="info-row"><span class="info-lbl">Nama</span><span class="info-val">{{ $karyawan->nama }}</span></div>
                <div class="info-row"><span class="info-lbl">NIK</span><span class="nik-badge">{{ $karyawan->nik }}</span></div>
                <div class="info-row"><span class="info-lbl">Email</span><span class="info-val">{{ $karyawan->email }}</span></div>
                <div class="info-row"><span class="info-lbl">Divisi</span><span class="info-val">{{ $karyawan->divisi }}</span></div>
                <div class="info-row"><span class="info-lbl">Jabatan</span><span class="info-val">{{ $karyawan->jabatan }}</span></div>
            @else
                <p style="color:var(--text-muted);font-size:0.875rem;">Data karyawan tidak ditemukan.</p>
            @endif
        </div>
    </div>

    <div class="card">
        <div class="card-head">
            <div class="card-head-icon" style="background:var(--success-light);color:var(--success);"><i class="fas fa-lock"></i></div>
            <h3 style="font-size:0.95rem;font-weight:700;">Ganti Password</h3>
        </div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success"><i class="fas fa-circle-check"></i> {{ session('success') }}</div>
            @endif
            @if($errors->any())
                <div class="alert alert-error"><i class="fas fa-circle-xmark"></i> {{ $errors->first() }}</div>
            @endif
            <form method="POST" action="{{ route('profil.password') }}">
                @csrf @method('PUT')
                <div class="form-group">
                    <label class="form-label">Password Lama</label>
                    <div class="input-wrap">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" name="password_lama" id="pw1" class="form-input" placeholder="Masukkan password lama">
                        <button type="button" class="toggle-pw" onclick="togglePw('pw1','eye1')"><i class="fas fa-eye" id="eye1"></i></button>
                    </div>
                    @error('password_lama')<div class="invalid-feedback"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Password Baru</label>
                    <div class="input-wrap">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="password_baru" id="pw2" class="form-input" placeholder="8-10 karakter" minlength="8" maxlength="10">
                        <button type="button" class="toggle-pw" onclick="togglePw('pw2','eye2')"><i class="fas fa-eye" id="eye2"></i></button>
                    </div>
                    @error('password_baru')<div class="invalid-feedback"><i class="fas fa-circle-exclamation"></i> {{ $message }}</div>@enderror
                </div>
                <div class="form-group">
                    <label class="form-label">Konfirmasi Password Baru</label>
                    <div class="input-wrap">
                        <i class="fas fa-key input-icon"></i>
                        <input type="password" name="password_baru_confirmation" id="pw3" class="form-input" placeholder="Ulangi password baru" minlength="8" maxlength="10">
                        <button type="button" class="toggle-pw" onclick="togglePw('pw3','eye3')"><i class="fas fa-eye" id="eye3"></i></button>
                    </div>
                </div>
                <button type="submit" class="btn-save"><i class="fas fa-floppy-disk"></i> Simpan Password</button>
            </form>
        </div>
    </div>
</main>

<script>
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('sidebarOverlay').classList.toggle('active');
}
function togglePw(inputId, eyeId) {
    const input = document.getElementById(inputId);
    const eye = document.getElementById(eyeId);
    input.type = input.type === 'password' ? 'text' : 'password';
    eye.className = input.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
}
setInterval(() => {
    const d = new Date();
    const el = document.getElementById('liveClock');
    if(el) el.textContent = d.toLocaleTimeString('id-ID', { hour12: false });
}, 1000);
</script>
</body>
</html>
