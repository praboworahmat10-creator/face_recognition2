<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Dosen</title>
    <link href="https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;600;700&family=DM+Sans:wght@300;400;500&display=swap" rel="stylesheet">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        :root {
            --navy:   #0f1f3d;
            --navy2:  #162847;
            --gold:   #c9a84c;
            --gold2:  #e8c96a;
            --cream:  #f5f0e8;
            --white:  #ffffff;
            --gray:   #8a95a3;
            --error:  #e05252;
        }

        body {
            min-height: 100vh;
            display: flex;
            font-family: 'DM Sans', sans-serif;
            background: var(--navy);
            overflow: hidden;
        }

        /* ── Left panel ── */
        .left {
            flex: 1;
            position: relative;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            padding: 60px;
            overflow: hidden;
        }

        .left::before {
            content: '';
            position: absolute;
            inset: 0;
            background:
                radial-gradient(ellipse 80% 60% at 20% 80%, rgba(201,168,76,.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 80% at 80% 10%, rgba(201,168,76,.10) 0%, transparent 55%);
        }

        /* decorative grid lines */
        .left::after {
            content: '';
            position: absolute;
            inset: 0;
            background-image:
                linear-gradient(rgba(201,168,76,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(201,168,76,.06) 1px, transparent 1px);
            background-size: 48px 48px;
        }

        .left-content {
            position: relative;
            z-index: 1;
            text-align: center;
            color: var(--white);
        }

        .logo-wrap {
            width: 90px; height: 90px;
            border-radius: 50%;
            background: rgba(201,168,76,.12);
            border: 2px solid rgba(201,168,76,.35);
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 28px;
            anikation: pulse 3s ease-in-out infinite;
        }

        .logo-wrap img {
            width: 58px; height: 58px;
            object-fit: contain;
        }

        /* fallback if no logo */
        .logo-icon {
            font-size: 38px;
        }

        @keyframes pulse {
            0%,100% { box-shadow: 0 0 0 0 rgba(201,168,76,.25); }
            50%      { box-shadow: 0 0 0 16px rgba(201,168,76,0); }
        }

        .left-content h1 {
            font-family: 'Playfair Display', serif;
            font-size: 2.6rem;
            font-weight: 700;
            line-height: 1.18;
            margin-bottom: 14px;
            background: linear-gradient(135deg, var(--white) 0%, var(--gold2) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .left-content p {
            font-size: .95rem;
            color: rgba(255,255,255,.55);
            line-height: 1.7;
            max-width: 280px;
            margin: 0 auto;
        }

        .divider-line {
            width: 50px; height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 22px auto;
        }

        /* floating badges */
        .badge {
            display: inline-flex; align-items: center; gap: 8px;
            background: rgba(255,255,255,.06);
            border: 1px solid rgba(201,168,76,.2);
            border-radius: 999px;
            padding: 7px 16px;
            font-size: .78rem;
            color: rgba(255,255,255,.7);
            margin: 4px;
        }
        .badge-dot { width:6px; height:6px; border-radius:50%; background: var(--gold); }

        /* ── Right panel ── */
        .right {
            width: 480px;
            min-height: 100vh;
            background: var(--cream);
            display: flex;
            flex-direction: column;
            justify-content: center;
            padding: 60px 50px;
            position: relative;
            anikation: slideIn .6s cubic-bezier(.22,1,.36,1) both;
        }

        @keyframes slideIn {
            from { transform: translateX(40px); opacity: 0; }
            to   { transform: translateX(0);    opacity: 1; }
        }

        /* top accent bar */
        .right::before {
            content: '';
            position: absolute;
            top: 0; left: 0; right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--navy), var(--gold), var(--navy));
        }

        .form-header { margin-bottom: 36px; }

        .form-header .eyebrow {
            font-size: .72rem;
            font-weight: 500;
            letter-spacing: .12em;
            text-transform: uppercase;
            color: var(--gold);
            margin-bottom: 8px;
        }

        .form-header h2 {
            font-family: 'Playfair Display', serif;
            font-size: 2rem;
            font-weight: 700;
            color: var(--navy);
            line-height: 1.2;
        }

        .form-header p {
            margin-top: 8px;
            font-size: .875rem;
            color: var(--gray);
        }

        /* error alert */
        .alert-error {
            background: #fff0f0;
            border-left: 3px solid var(--error);
            border-radius: 6px;
            padding: 12px 16px;
            font-size: .83rem;
            color: var(--error);
            margin-bottom: 24px;
        }

        /* form fields */
        .field { margin-bottom: 22px; }

        .field label {
            display: block;
            font-size: .8rem;
            font-weight: 500;
            letter-spacing: .04em;
            color: var(--navy);
            margin-bottom: 8px;
        }

        .input-wrap {
            position: relative;
        }

        .input-wrap svg {
            position: absolute;
            left: 14px; top: 50%; transform: translateY(-50%);
            color: var(--gray);
            width: 18px; height: 18px;
            pointer-events: none;
        }

        .input-wrap input {
            width: 100%;
            padding: 13px 14px 13px 44px;
            border: 1.5px solid #ddd5c8;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .93rem;
            color: var(--navy);
            background: var(--white);
            transition: border-color .2s, box-shadow .2s;
            outline: none;
        }

        .input-wrap input:focus {
            border-color: var(--gold);
            box-shadow: 0 0 0 3px rgba(201,168,76,.15);
        }

        .input-wrap input.is-invalid {
            border-color: var(--error);
        }

        .field-error {
            font-size: .78rem;
            color: var(--error);
            margin-top: 5px;
        }

        /* show/hide password toggle */
        .toggle-pw {
            position: absolute;
            right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: var(--gray); padding: 0;
            display: flex; align-items: center;
        }
        .toggle-pw:hover { color: var(--navy); }

        /* remember + forgot row */
        .row-meta {
            display: flex; align-items: center; justify-content: space-between;
            margin-bottom: 28px;
        }

        .remember {
            display: flex; align-items: center; gap: 8px;
            font-size: .83rem; color: var(--gray); cursor: pointer;
        }

        .remember input[type=checkbox] { accent-color: var(--gold); width:15px; height:15px; }

        /* submit button */
        .btn-login {
            width: 100%;
            padding: 14px;
            background: linear-gradient(135deg, var(--navy) 0%, var(--navy2) 100%);
            color: var(--white);
            border: none;
            border-radius: 10px;
            font-family: 'DM Sans', sans-serif;
            font-size: .95rem;
            font-weight: 500;
            letter-spacing: .03em;
            cursor: pointer;
            position: relative;
            overflow: hidden;
            transition: transform .15s, box-shadow .15s;
        }

        .btn-login::after {
            content: '';
            position: absolute;
            inset: 0;
            background: linear-gradient(135deg, transparent 40%, rgba(201,168,76,.18));
            opacity: 0;
            transition: opacity .3s;
        }

        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 24px rgba(15,31,61,.25); }
        .btn-login:hover::after { opacity: 1; }
        .btn-login:active { transform: translateY(0); }

        /* gold accent line under button */
        .gold-line {
            height: 2px;
            background: linear-gradient(90deg, transparent, var(--gold), transparent);
            margin: 28px 0 24px;
            opacity: .4;
        }

        .footer-note {
            text-align: center;
            font-size: .78rem;
            color: var(--gray);
        }

        .footer-note a { color: var(--navy); font-weight: 500; text-decoration: none; }
        .footer-note a:hover { color: var(--gold); }

        @media (max-width: 860px) {
            .left { display: none; }
            .right { width: 100%; }
        }
    </style>
</head>
<body>

{{-- ── Left panel ── --}}
<div class="left">
    <div class="left-content">
        <div class="logo-wrap">
            @if(file_exists(public_path('images/logo_kampus.png')))
                <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo Kampus">
            @else
                <span class="logo-icon">🎓</span>
            @endif
        </div>

        <h1>Portal<br>Akademik</h1>
        <div class="divider-line"></div>
        <p>Sistem presensi berbasis pengenalan wajah untuk tenaga pengajar</p>

        <div style="margin-top: 32px;">
            <span class="badge"><span class="badge-dot"></span> Face Recognition</span>
            <span class="badge"><span class="badge-dot"></span> Real-time</span>
            <span class="badge"><span class="badge-dot"></span> Aman & Terenkripsi</span>
        </div>
    </div>
</div>

{{-- ── Right panel (form) ── --}}
<div class="right">
    <div class="form-header">
        <p class="eyebrow">Area Dosen</p>
        <h2>Selamat Datang</h2>
        <p>Masuk menggunakan NIP dan kata sandi Anda</p>
    </div>

    {{-- Error global --}}
    @if ($errors->any())
        <div class="alert-error">
            {{ $errors->first() }}
        </div>
    @endif

    <form method="POST" action="{{ route('dosen.login') }}" autocomplete="off">
        @csrf

        {{-- NIP --}}
        <div class="field">
            <label for="nip">Nomor Induk Pegawai (NIP)</label>
            <div class="input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z"/>
                </svg>
                <input
                    type="text"
                    id="nip"
                    name="nip"
                    value="{{ old('nip') }}"
                    placeholder="Contoh: 198001012010011001"
                    class="{{ $errors->has('nip') ? 'is-invalid' : '' }}"
                    autofocus
                    required
                >
            </div>
            @error('nip')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Password --}}
        <div class="field">
            <label for="password">Kata Sandi</label>
            <div class="input-wrap">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 10-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 002.25-2.25v-6.75a2.25 2.25 0 00-2.25-2.25H6.75a2.25 2.25 0 00-2.25 2.25v6.75a2.25 2.25 0 002.25 2.25z"/>
                </svg>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Masukkan kata sandi"
                    class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                    required
                >
                <button type="button" class="toggle-pw" onclick="togglePassword()" title="Tampilkan/sembunyikan">
                    <svg id="eye-icon" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" width="18" height="18">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 010-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </button>
            </div>
            @error('password')
                <p class="field-error">{{ $message }}</p>
            @enderror
        </div>

        {{-- Remember --}}
        <div class="row-meta">
            <label class="remember">
                <input type="checkbox" name="remember"> Ingat saya
            </label>
        </div>

        <button type="submit" class="btn-login">Masuk</button>
    </form>

    <div class="gold-line"></div>

    <p class="footer-note">
        Bukan dosen? <a href="{{ route('login') }}">Login sebagai Admin</a>
    </p>
</div>

<script>
function togglePassword() {
    const input = document.getElementById('password');
    input.type = input.type === 'password' ? 'text' : 'password';
}
</script>

</body>
</html>