<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Koperasi Kantor Pos Indonesia</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        *, *::before, *::after { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Inter', sans-serif;
            background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .login-container {
            width: 100%;
            max-width: 420px;
        }

        .brand {
            text-align: center;
            margin-bottom: 24px;
        }

        .brand img {
            height: 80px;
            width: auto;
            object-fit: contain;
            margin-bottom: 20px;
        }

        .brand-title {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
        }

        .brand-subtitle {
            font-size: 13px;
            color: #64748b;
            margin-top: 4px;
        }

        .card {
            background: #ffffff;
            border: 1px solid #ffffff;
            border-top: 4px solid #f97316;
            border-radius: 16px;
            padding: 40px 32px;
            box-shadow: 0 20px 40px -15px rgba(249,115,22,0.15), 0 4px 6px -1px rgba(0,0,0,0.05);
        }

        .card-header {
            margin-bottom: 24px;
            text-align: center;
        }

        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #0f172a;
        }

        .alert {
            padding: 12px 14px;
            border-radius: 8px;
            font-size: 13px;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 8px;
        }
        .alert-error { background: #fef2f2; border: 1px solid #fecaca; color: #ef4444; }
        .alert-success { background: #f0fdf4; border: 1px solid #bbf7d0; color: #10b981; }

        .form-group { margin-bottom: 16px; }
        
        .form-label {
            display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px;
        }

        .input-wrap { position: relative; }
        
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 13px; transition: color 0.2s;
        }

        .form-input {
            width: 100%; padding: 12px 14px 12px 40px; background: #f8fafc;
            border: 1px solid #e2e8f0; border-radius: 8px; color: #0f172a;
            font-size: 14px; font-family: 'Inter', sans-serif; transition: all 0.2s; outline: none;
        }
        .form-input:focus { background: #ffffff; border-color: #f97316; box-shadow: 0 0 0 3px rgba(249,115,22,0.1); }
        .input-wrap:focus-within .input-icon { color: #f97316; }
        .form-input.is-invalid { border-color: #ef4444; }

        .toggle-pwd {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; cursor: pointer; font-size: 13px; transition: color 0.2s; background: none; border: none;
        }
        .toggle-pwd:hover { color: #475569; }

        .options-row {
            display: flex; align-items: center; justify-content: space-between; margin-bottom: 24px; margin-top: -4px;
        }
        .check-label {
            display: flex; align-items: center; gap: 8px; font-size: 13px; color: #64748b; cursor: pointer; user-select: none;
        }
        .check-label input { display: none; }
        .check-box {
            width: 16px; height: 16px; border: 1.5px solid #cbd5e1; border-radius: 4px;
            display: flex; align-items: center; justify-content: center; transition: all .2s;
            font-size: 10px; color: transparent; flex-shrink: 0;
        }
        .check-label input:checked ~ .check-box { background: #f97316; border-color: #f97316; color: white; }

        .forgot-link { font-size: 13px; color: #f97316; text-decoration: none; font-weight: 500; }
        .forgot-link:hover { color: #ea580c; }

        .btn-submit {
            width: 100%; padding: 12px; background: #f97316;
            color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500;
            font-family: 'Inter', sans-serif; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-submit:hover { background: #ea580c; }
        .btn-submit:disabled { opacity: 0.7; cursor: not-allowed; }

        .spinner { width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3); border-top-color: white; border-radius: 50%; animation: spin .6s linear infinite; display: none; }
        @keyframes spin { to { transform: rotate(360deg); } }

        .divider { display: flex; align-items: center; gap: 14px; margin: 24px 0; color: #94a3b8; font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }
        
        .register-row { text-align: center; font-size: 13px; color: #64748b; }
        .register-row a { color: #f97316; font-weight: 500; text-decoration: none; }
        .register-row a:hover { color: #ea580c; }

        .mobile-access-box {
            margin-top: 20px; padding: 14px; border-radius: 12px;
            background: #eff6ff; border: 1px solid #bfdbfe; text-align: left;
        }
        .mobile-access-title { font-size: 13px; font-weight: 700; color: #1e40af; margin-bottom: 8px; }
        .mobile-access-note { font-size: 12px; color: #475569; margin-bottom: 10px; line-height: 1.5; }
        .mobile-access-url { margin-bottom: 8px; }
        .mobile-access-url .url-label { display: block; font-size: 11px; color: #64748b; margin-bottom: 2px; }
        .mobile-access-url a {
            font-size: 12px; font-weight: 600; color: #2563eb; word-break: break-all; text-decoration: none;
        }
        .mobile-access-url.recommended a { color: #059669; }
        .mobile-access-hint { font-size: 11px; color: #64748b; line-height: 1.5; margin-top: 8px; }
        .mobile-access-hint code { background: #dbeafe; padding: 2px 6px; border-radius: 4px; font-size: 10px; }

        .footer-note { text-align: center; margin-top: 24px; font-size: 12px; color: #94a3b8; }
    </style>
</head>
<body>

<div class="login-container">
    <div class="brand">
        <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo KOPPOSINDO">
        <h1 class="brand-title">Koperasi Kantor Pos Indonesia</h1>
        <p class="brand-subtitle">Sistem Presensi Karyawan</p>
    </div>

    <div class="card">
        <div class="card-header">
            <h2 class="card-title">Masuk Akun</h2>
        </div>

        @if (session('status'))
            <div class="alert alert-success">
                <i class="fas fa-circle-check"></i> {{ session('status') }}
            </div>
        @endif

        @if(session('success'))
            <div class="alert alert-success" style="background:#dcfce7; color:#16a34a; border:1px solid #bbf7d0; padding:12px 16px; border-radius:10px; margin-bottom:16px; font-size:13px; display:flex; align-items:center; gap:8px;">
                <i class="fas fa-circle-check"></i> {{ session('success') }}
            </div>
        @endif

        @if ($errors->any())
            <div class="alert alert-error">
                <i class="fas fa-circle-exclamation"></i> {{ $errors->first() }}
            </div>
        @endif

        <form method="POST" action="{{ route('login') }}" id="loginForm">
            @csrf

            <!-- Role Selector -->
            <div class="form-group">
                <label class="form-label">Masuk Sebagai</label>
                <div style="display: flex; gap: 15px;">
                    <label style="color: #475569; font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="login_role" value="karyawan" {{ old('login_role', 'karyawan') == 'karyawan' ? 'checked' : '' }} onchange="toggleLoginRole('karyawan')"> Karyawan (NIK)
                    </label>
                    <label style="color: #475569; font-size: 13px; display: flex; align-items: center; gap: 6px; cursor: pointer;">
                        <input type="radio" name="login_role" value="admin" {{ old('login_role') == 'admin' ? 'checked' : '' }} onchange="toggleLoginRole('admin')"> Admin (Email)
                    </label>
                </div>
            </div>

            <!-- NIK (Karyawan) -->
            <div class="form-group" id="nik-group">
                <label class="form-label" for="nik">NIK Karyawan</label>
                <div class="input-wrap">
                    <input type="text" id="nik" name="nik" class="form-input {{ $errors->has('nik') ? 'is-invalid' : '' }}" placeholder="Masukkan NIK" value="{{ old('nik') }}" autofocus autocomplete="username" maxlength="20" inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                    <i class="fas fa-id-badge input-icon"></i>
                </div>
            </div>

            <!-- Email (Admin) -->
            <div class="form-group" id="email-group" style="display: none;">
                <label class="form-label" for="email">Email Admin</label>
                <div class="input-wrap">
                    <input type="email" id="email" name="email" class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="Masukkan Email" value="{{ old('email') }}" autocomplete="email">
                    <i class="fas fa-envelope input-icon"></i>
                </div>
            </div>

            <!-- Password -->
            <div class="form-group">
                <label class="form-label" for="password">Password</label>
                <div class="input-wrap">
                    <input type="password" id="password" name="password" class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="Masukkan password" minlength="8" maxlength="10" required autocomplete="current-password">
                    <i class="fas fa-lock input-icon"></i>
                    <button type="button" class="toggle-pwd" onclick="togglePwd()">
                        <i class="fas fa-eye" id="eyeIcon"></i>
                    </button>
                </div>
            </div>

            <div class="options-row">
                <label class="check-label">
                    <input type="checkbox" name="remember" {{ old('remember') ? 'checked' : '' }}>
                    <div class="check-box"><i class="fas fa-check"></i></div>
                    Ingat saya
                </label>
                <a href="{{ route('password.request') }}" class="forgot-link">Lupa password?</a>
            </div>

            <button type="submit" class="btn-submit" id="submitBtn">
                <span id="btnText">Masuk</span>
                <div class="spinner" id="spinner"></div>
            </button>
        </form>

        <div class="divider">atau</div>
        <div class="register-row">
            Belum punya akun karyawan? <a href="{{ route('register') }}">Daftar sekarang</a>
        </div>

        @if(config('app.debug') && $lanIp)
        <div class="mobile-access-box">
            <div class="mobile-access-title"><i class="fas fa-mobile-screen"></i> Akses dari HP</div>
            <p class="mobile-access-note">HP harus terhubung <strong>WiFi yang sama</strong> dengan Mac/laptop.</p>
            @if($mobileHttpsUrl)
            <div class="mobile-access-url recommended">
                <span class="url-label">Untuk kamera presensi (HTTPS):</span>
                <a href="{{ $mobileHttpsUrl }}">{{ $mobileHttpsUrl }}</a>
            </div>
            @endif
            @if($mobileHttpUrl)
            <div class="mobile-access-url">
                <span class="url-label">Hanya lihat dashboard (HTTP):</span>
                <a href="{{ $mobileHttpUrl }}">{{ $mobileHttpUrl }}</a>
            </div>
            @endif
            <p class="mobile-access-hint">Jalankan di terminal: <code>npm run serve:mobile</code><br>
            Browser HP akan memperingatkan sertifikat — pilih <strong>Lanjutkan / Advanced → Proceed</strong>.</p>
        </div>
        @endif
    </div>

    <div class="footer-note">
        &copy; {{ date('Y') }} Sistem Presensi Koperasi Kantor Pos Indonesia
    </div>
</div>

<script>
    function toggleLoginRole(role) {
        const nikGroup = document.getElementById('nik-group');
        const emailGroup = document.getElementById('email-group');
        
        if (role === 'karyawan') {
            nikGroup.style.display = 'block';
            emailGroup.style.display = 'none';
        } else {
            nikGroup.style.display = 'none';
            emailGroup.style.display = 'block';
        }
    }

    window.addEventListener('DOMContentLoaded', () => {
        const checkedRole = document.querySelector('input[name="login_role"]:checked');
        if (checkedRole) toggleLoginRole(checkedRole.value);
    });

    function togglePwd() {
        const inp = document.getElementById('password');
        const ico = document.getElementById('eyeIcon');
        inp.type = inp.type === 'password' ? 'text' : 'password';
        ico.className = inp.type === 'password' ? 'fas fa-eye' : 'fas fa-eye-slash';
    }

    document.getElementById('loginForm').addEventListener('submit', function() {
        const btn = document.getElementById('submitBtn');
        document.getElementById('btnText').style.display = 'none';
        document.getElementById('spinner').style.display = 'block';
        btn.disabled = true;
    });
</script>
</body>
</html>
