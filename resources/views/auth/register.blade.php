<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Karyawan — Presensi</title>
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

        .register-container {
            width: 100%;
            max-width: 540px;
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

        .card-header { margin-bottom: 24px; text-align: center; }
        .card-title { font-size: 18px; font-weight: 600; color: #0f172a; margin-bottom: 6px; }
        .card-desc { font-size: 13px; color: #64748b; }

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

        .form-row { margin-bottom: 16px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
        .form-group { margin-bottom: 0; }

        .form-label { display: block; font-size: 13px; font-weight: 500; color: #475569; margin-bottom: 6px; }
        .form-label span { color: #ef4444; margin-left: 2px; }

        .input-wrapper { position: relative; }
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
        .input-wrapper:focus-within .input-icon { color: #f97316; }
        .form-input.is-invalid { border-color: #ef4444; }

        .toggle-password {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; cursor: pointer; font-size: 13px; transition: color 0.2s; background: none; border: none;
        }
        .toggle-password:hover { color: #475569; }

        .btn-primary {
            width: 100%; padding: 12px; background: #f97316;
            color: white; border: none; border-radius: 8px; font-size: 14px; font-weight: 500;
            font-family: 'Inter', sans-serif; cursor: pointer; transition: all 0.2s;
            display: flex; align-items: center; justify-content: center; gap: 8px;
        }
        .btn-primary:hover { background: #ea580c; }
        .btn-primary:disabled { opacity: 0.7; cursor: not-allowed; }

        .divider { display: flex; align-items: center; gap: 14px; margin: 24px 0; color: #94a3b8; font-size: 12px; }
        .divider::before, .divider::after { content: ''; flex: 1; height: 1px; background: #e2e8f0; }

        .login-link { text-align: center; font-size: 13px; color: #64748b; }
        .login-link a { color: #f97316; text-decoration: none; font-weight: 500; }
        .login-link a:hover { color: #ea580c; }

        .footer-note { text-align: center; margin-top: 24px; font-size: 12px; color: #94a3b8; }

        .spinner {
            display: none; width: 16px; height: 16px; border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white; border-radius: 50%; animation: spin 0.6s linear infinite; margin: 0 auto;
        }
        @keyframes spin { to { transform: rotate(360deg); } }

        /* Password Strength */
        .pwd-indicator {
            margin-top: 8px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            font-size: 11px;
            font-weight: 500;
        }
        .pwd-bars {
            display: flex;
            gap: 4px;
            flex: 1;
            margin-right: 12px;
        }
        .pwd-bar {
            height: 4px;
            flex: 1;
            background: #e2e8f0;
            border-radius: 2px;
            transition: all 0.3s;
        }
        .pwd-text {
            color: #94a3b8;
            min-width: 45px;
            text-align: right;
        }
        .strength-1 .pwd-bar:nth-child(1) { background: #ef4444; }
        .strength-1 .pwd-text { color: #ef4444; }
        
        .strength-2 .pwd-bar:nth-child(1), .strength-2 .pwd-bar:nth-child(2) { background: #eab308; }
        .strength-2 .pwd-text { color: #eab308; }
        
        .strength-3 .pwd-bar { background: #22c55e; }
        .strength-3 .pwd-text { color: #22c55e; }

        @media (max-width: 500px) {
            .form-grid { grid-template-columns: 1fr; }
            .card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="brand">
            <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo KOPPOSINDO">
            <h1 class="brand-title">Koperasi Kantor Pos Indonesia</h1>
            <p class="brand-subtitle">Sistem Presensi Karyawan</p>
        </div>

        <div class="card">
            <div class="card-header">
                <h2 class="card-title">Buat Akun Baru</h2>
                <p class="card-desc">Isi form di bawah ini untuk mendaftar sebagai karyawan</p>
            </div>

            @if ($errors->any())
                <div class="alert alert-error">
                    <i class="fas fa-exclamation-circle"></i>
                    {{ $errors->first() }}
                </div>
            @endif

            <form method="POST" action="{{ route('register') }}" id="registerForm">
                @csrf

                <!-- Nama & NIK -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="nama">Nama Lengkap <span>*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user input-icon"></i>
                            <input type="text" id="nama" name="nama" class="form-input {{ $errors->has('nama') ? 'is-invalid' : '' }}" placeholder="Nama sesuai KTP" value="{{ old('nama') }}" required autofocus>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="nik">NIK Karyawan <span>*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" id="nik" name="nik" class="form-input {{ $errors->has('nik') ? 'is-invalid' : '' }}" placeholder="Nomor Induk Karyawan" value="{{ old('nik') }}" maxlength="20" required inputmode="numeric" pattern="[0-9]*" oninput="this.value = this.value.replace(/[^0-9]/g, '')">
                        </div>
                    </div>
                </div>

                <!-- Email -->
                <div class="form-row">
                    <label class="form-label" for="email">Alamat Email <span>*</span></label>
                    <div class="input-wrapper">
                        <i class="fas fa-envelope input-icon"></i>
                        <input type="email" id="email" name="email" class="form-input {{ $errors->has('email') ? 'is-invalid' : '' }}" placeholder="nama@email.com" value="{{ old('email') }}" required>
                    </div>
                </div>

                <!-- Divisi & Jabatan -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="divisi">Divisi <span>*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-sitemap input-icon"></i>
                            <select id="divisi" name="divisi" class="form-input {{ $errors->has('divisi') ? 'is-invalid' : '' }}" required style="cursor:pointer; appearance:auto;">
                                <option value="" disabled {{ old('divisi') ? '' : 'selected' }}>Pilih Divisi</option>
                                @foreach(['Toko', 'IT', 'Induk', 'Gudang', 'Operasional', 'Umum', 'Jasa Kirim Pos', 'BMS', 'Teksar', 'Simpan Pinjam', 'Super Admin'] as $div)
                                    <option value="{{ $div }}" {{ old('divisi') == $div ? 'selected' : '' }}>{{ $div }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="jabatan">Jabatan <span>*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-user-tie input-icon"></i>
                            <select id="jabatan" name="jabatan" class="form-input {{ $errors->has('jabatan') ? 'is-invalid' : '' }}" required style="cursor:pointer; appearance:auto;">
                                <option value="" disabled {{ old('jabatan') ? '' : 'selected' }}>Pilih Jabatan</option>
                                @foreach(['Manajer', 'Ass. Manajer', 'Staff', 'Pelaksana', 'Admin'] as $jab)
                                    <option value="{{ $jab }}" {{ old('jabatan') == $jab ? 'selected' : '' }}>{{ $jab }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>

                <!-- Password & Konfirmasi -->
                <div class="form-grid">
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span>*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password" name="password" class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}" placeholder="8-10 karakter" minlength="8" maxlength="10" required>
                            <button type="button" class="toggle-password" onclick="togglePass('password', 'eye1')">
                                <i class="fas fa-eye" id="eye1"></i>
                            </button>
                        </div>
                        <div class="pwd-indicator" id="pwdIndicator">
                            <div class="pwd-bars">
                                <div class="pwd-bar"></div>
                                <div class="pwd-bar"></div>
                                <div class="pwd-bar"></div>
                            </div>
                            <div class="pwd-text" id="pwdText">Lemah</div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="password_confirmation">Konfirmasi <span>*</span></label>
                        <div class="input-wrapper">
                            <i class="fas fa-lock input-icon"></i>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="form-input" placeholder="Ulangi password" minlength="8" maxlength="10" required>
                            <button type="button" class="toggle-password" onclick="togglePass('password_confirmation', 'eye2')">
                                <i class="fas fa-eye" id="eye2"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <button type="submit" class="btn-primary" id="submitBtn">
                    <span id="btn-text">Daftar Akun Karyawan</span>
                    <div class="spinner" id="spinner"></div>
                </button>
            </form>

            <div class="divider">atau</div>

            <div class="login-link">
                Sudah mendaftar? <a href="{{ route('login') }}">Masuk di sini</a>
            </div>
        </div>

        <div class="footer-note">
            &copy; {{ date('Y') }} Sistem Presensi Koperasi Kantor Pos Indonesia
        </div>
    </div>

    <script>
        function togglePass(inputId, iconId) {
            const input = document.getElementById(inputId);
            const icon = document.getElementById(iconId);
            
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                input.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }

        document.getElementById('registerForm').addEventListener('submit', function() {
            const btn = document.getElementById('submitBtn');
            const text = document.getElementById('btn-text');
            const spinner = document.getElementById('spinner');

            text.style.display = 'none';
            spinner.style.display = 'block';
            btn.disabled = true;
        });

        // Password Strength Checker
        document.getElementById('password').addEventListener('input', function(e) {
            const val = e.target.value;
            const indicator = document.getElementById('pwdIndicator');
            const text = document.getElementById('pwdText');
            
            // Reset classes
            indicator.className = 'pwd-indicator';
            
            if (val.length === 0) {
                text.textContent = 'Lemah';
                return;
            }

            let strength = 0;
            if (val.length >= 8) strength += 1;
            if (val.match(/[a-z]+/)) strength += 1;
            if (val.match(/[A-Z]+/)) strength += 1;
            if (val.match(/[0-9]+/)) strength += 1;
            if (val.match(/[$@#&!]+/)) strength += 1;

            if (strength <= 2) {
                indicator.classList.add('strength-1');
                text.textContent = 'Lemah';
            } else if (strength === 3 || strength === 4) {
                indicator.classList.add('strength-2');
                text.textContent = 'Sedang';
            } else {
                indicator.classList.add('strength-3');
                text.textContent = 'Kuat';
            }
        });
    </script>
</body>
</html>
