<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Daftar Wajah — PresensiKu</title>
    <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'DM Sans', sans-serif;
            background: linear-gradient(135deg, #fff7ed 0%, #fed7aa 100%);
            background-attachment: fixed;
            min-height: 100vh;
            color: #0f172a;
            font-size: 14px;
        }
        .topbar {
            background: #ffffff;
            height: 56px;
            display: flex;
            align-items: center;
            padding: 0 24px;
            justify-content: space-between;
            position: sticky; top: 0; z-index: 100;
            border-bottom: 1px solid #fed7aa;
        }
        .topbar-brand { display: flex; align-items: center; gap: 10px; }
        .brand-logo {
            width: 32px; height: 32px;
            background: linear-gradient(135deg, #f97316, #10b981);
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 14px; color: white;
        }
        .brand-name { font-size: 15px; font-weight: 700; color: #0f172a; }
        .topbar-right { display: flex; align-items: center; gap: 14px; }
        .topbar-link {
            color: #475569; font-size: 13px; text-decoration: none;
            display: flex; align-items: center; gap: 6px;
            transition: color 0.15s;
        }
        .topbar-link:hover { color: #ea580c; }
        .content {
            max-width: 800px;
            margin: 32px auto;
            padding: 0 24px;
        }
        .page-title { font-size: 22px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
        .page-sub { color: #475569; font-size: 13px; margin-bottom: 24px; }
        .card {
            background: rgba(255,255,255,0.95);
            border-radius: 14px;
            border: 1px solid rgba(255,255,255,0.6);
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            overflow: hidden;
            margin-bottom: 20px;
        }
        .card-header {
            padding: 14px 20px;
            border-bottom: 1px solid #e2e8f0;
            display: flex; align-items: center; gap: 10px;
        }
        .card-icon {
            width: 30px; height: 30px; border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            font-size: 13px;
        }
        .card-icon.blue { background: #dbeafe; color: #ea580c; }
        .card-icon.green { background: #d1fae5; color: #059669; }
        .card-title { font-size: 13.5px; font-weight: 600; color: #0f172a; }
        .card-body { padding: 20px; }

        /* Status Banner */
        .status-banner {
            display: flex; align-items: center; gap: 12px;
            padding: 14px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 13px;
        }
        .status-banner.registered {
            background: #d1fae5; border: 1px solid #a7f3d0; color: #065f46;
        }
        .status-banner.unregistered {
            background: #fef3c7; border: 1px solid #fde68a; color: #92400e;
        }
        .status-icon { font-size: 20px; }

        /* Camera */
        .camera-wrap {
            position: relative;
            background: #0a0a0a;
            border-radius: 10px;
            overflow: hidden;
            aspect-ratio: 4/3;
            margin-bottom: 14px;
        }
        #video {
            width: 100%; height: 100%;
            object-fit: cover;
            transform: scaleX(-1);
        }
        .cam-corner {
            position: absolute;
            width: 24px; height: 24px;
            border-color: #f97316; border-style: solid;
            z-index: 5;
        }
        .cam-corner.tl { top: 12px; left: 12px; border-width: 2px 0 0 2px; border-radius: 3px 0 0 0; }
        .cam-corner.tr { top: 12px; right: 12px; border-width: 2px 2px 0 0; border-radius: 0 3px 0 0; }
        .cam-corner.bl { bottom: 12px; left: 12px; border-width: 0 0 2px 2px; border-radius: 0 0 0 3px; }
        .cam-corner.br { bottom: 12px; right: 12px; border-width: 0 2px 2px 0; border-radius: 0 0 3px 0; }
        .cam-corner.scanning { border-color: #10b981; width: 32px; height: 32px; }

        .scan-overlay {
            position: absolute; inset: 0;
            pointer-events: none; display: none; z-index: 3;
        }
        .scan-overlay.active { display: block; }
        .scan-beam {
            position: absolute; left: 0; width: 100%; height: 3px;
            background: linear-gradient(90deg, transparent, #f97316, transparent);
            anikation: scanBeam 1.8s ease-in-out infinite;
        }
        @keyframes scanBeam {
            0% { top: 0; opacity: 0.8; }
            100% { top: 100%; opacity: 0; }
        }

        .success-overlay {
            position: absolute; inset: 0;
            background: rgba(5,150,105,0.15);
            display: none; align-items: center; justify-content: center;
            flex-direction: column; gap: 10px; z-index: 6;
        }
        .success-overlay.show { display: flex; }
        .success-check {
            width: 64px; height: 64px;
            background: #059669; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            color: white; font-size: 26px;
            anikation: popIn 0.4s cubic-bezier(0.175,0.885,0.32,1.275);
        }
        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to { transform: scale(1); opacity: 1; }
        }
        .success-text {
            font-size: 14px; font-weight: 700; color: #059669;
            background: white; padding: 6px 16px; border-radius: 20px;
        }

        /* Foto Preview */
        .foto-preview {
            width: 80px; height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #059669;
            display: none;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
        }
        .foto-preview.show { display: block; }

        /* Profile Header Style (match profil.blade.php) */
        .profile-header {
            display: flex;
            align-items: center;
            gap: 20px;
            background: rgba(255,255,255,0.95);
            border: 1px solid rgba(255,255,255,0.6);
            border-radius: 14px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.15);
            padding: 24px;
            margin-bottom: 20px;
        }
        .profile-avatar {
            width: 80px;
            height: 80px;
            border-radius: 50%;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            background: #d1fae5;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #059669;
            font-size: 32px;
        }
        .profile-info h2 {
            font-size: 20px;
            font-weight: 700;
            color: #0f172a;
            margin-bottom: 4px;
        }
        .profile-info p {
            color: #475569;
            font-size: 13.5px;
        }

        /* Steps */
        .steps {
            display: flex; gap: 8px; margin-bottom: 20px;
        }
        .step {
            flex: 1; padding: 10px; border-radius: 9px;
            text-align: center; font-size: 12px;
            border: 1px solid #e2e8f0;
            background: #f8fafc; color: #94a3b8;
        }
        .step.active { background: #ffedd5; border-color: #fed7aa; color: #ea580c; }
        .step.done { background: #d1fae5; border-color: #6ee7b7; color: #065f46; }
        .step i { display: block; font-size: 18px; margin-bottom: 4px; }

        /* Buttons */
        .btn {
            display: inline-flex; align-items: center; gap: 7px;
            padding: 11px 20px; border-radius: 9px;
            font-family: 'DM Sans', sans-serif; font-size: 13px;
            font-weight: 600; border: none; cursor: pointer;
            transition: all 0.15s;
        }
        .btn-primary { background: #ea580c; color: white; }
        .btn-primary:hover { background: #ea580c; }
        .btn-success { background: #059669; color: white; }
        .btn-success:hover { background: #047857; }
        .btn:disabled { background: #e2e8f0 !important; color: #94a3b8 !important; border: 1px solid #cbd5e1 !important; cursor: not-allowed; opacity: 1 !important; }
        .btn-secondary {
            background: #f1f5f9; color: #475569;
            border: 1px solid #e2e8f0;
        }
        .btn-secondary:hover { border-color: #ea580c; color: #ea580c; }
        .btn-block { width: 100%; justify-content: center; padding: 13px; font-size: 14px; }

        .alert {
            padding: 12px 16px; border-radius: 10px;
            font-size: 13px; margin-bottom: 16px;
            display: flex; align-items: center; gap: 10px;
        }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #a7f3d0; }
        .alert-error { background: #ffe4e6; color: #9f1239; border: 1px solid #fecdd3; }

        .spinner {
            width: 14px; height: 14px;
            border: 2px solid transparent;
            border-top-color: currentColor;
            border-radius: 50%;
            anikation: spin 0.7s linear infinite;
        }
        @keyframes spin { to { transform: rotate(360deg); } }
        @keyframes pulse { 0%,100%{opacity:1}50%{opacity:0.5} }
    </style>
</head>
<body>

<nav class="topbar">
    <div class="topbar-brand">
        <div class="brand-logo" style="background:none; height:34px; width:auto; display:flex; align-items:center;">
            <img src="{{ asset('images/logo_kampus.png') }}" alt="Logo" style="height:100%; width:auto; object-fit:contain;">
        </div>
        <span class="brand-name">Koperasi Kantor Pos Indonesia</span>
    </div>
    <div class="topbar-right">
        <a href="/presensi" class="topbar-link">
            <i class="fas fa-arrow-left"></i> Kembali ke Presensi
        </a>
    </div>
</nav>

<div class="content">
    <div class="page-title">Daftar Wajah</div>
    <div class="page-sub">Daftarkan wajah kamu untuk presensi otomatis</div>

    {{-- Status Pendaftaran --}}
    @if($karyawan && $karyawan->face_descriptors)
        <div class="status-banner registered">
            <div class="status-icon"><i class="fas fa-circle-check"></i></div>
            <div>
                <strong>Wajah sudah terdaftar!</strong>
                <div style="font-size:12px;margin-top:2px;">Data wajah sudah tersimpan. Hubungi admin jika ingin mengubah.</div>
            </div>
            @if($karyawan->foto)
                <img src="{{ Storage::url($karyawan->foto) }}" class="foto-preview show" style="margin-left:auto;">
            @endif
        </div>
    @else
        <div class="status-banner unregistered">
            <div class="status-icon"><i class="fas fa-triangle-exclamation"></i></div>
            <div>
                <strong>Wajah belum terdaftar!</strong>
                <div style="font-size:12px;margin-top:2px;">Daftarkan wajah kamu agar bisa melakukan presensi.</div>
            </div>
        </div>
    @endif

    <div id="alertBox"></div>

    <div class="card">
        <div class="card-header">
            <div class="card-icon blue"><i class="fas fa-camera"></i></div>
            <span class="card-title">Kamera Pendaftaran Wajah</span>
        </div>
        <div class="card-body">



            {{-- Steps --}}
            <div class="steps">
                <div class="step active" id="step1">
                    <i class="fas fa-camera"></i> Aktifkan Kamera
                </div>
                <div class="step" id="step2">
                    <i class="fas fa-face-smile"></i> Scan Wajah
                </div>
                <div class="step" id="step3">
                    <i class="fas fa-floppy-disk"></i> Simpan Data
                </div>
            </div>

            {{-- Camera --}}
            <div class="camera-wrap">
                <video id="video" autoplay muted playsinline></video>
                <canvas id="canvas" style="display:none;"></canvas>
                <div class="cam-corner tl" id="ccTL"></div>
                <div class="cam-corner tr" id="ccTR"></div>
                <div class="cam-corner bl" id="ccBL"></div>
                <div class="cam-corner br" id="ccBR"></div>
                <div class="scan-overlay" id="scanOverlay">
                    <div class="scan-beam"></div>
                </div>
                <div class="success-overlay" id="successOverlay">
                    <div class="success-check"><i class="fas fa-check"></i></div>
                    <div class="success-text">Wajah Terdeteksi!</div>
                </div>
            </div>

            {{-- Scan Status --}}
            <div id="scanStatus" style="font-size:12.5px;color:#64748b;margin-bottom:14px;display:flex;align-items:center;gap:6px;">
                <i class="fas fa-circle-dot"></i> Klik "Mulai Kamera" untuk memulai
            </div>

            {{-- Buttons --}}
            <div style="display:flex;gap:10px;flex-wrap:wrap;">
                <button class="btn btn-secondary" id="btnStart" onclick="startCamera()">
                    <i class="fas fa-play"></i> Mulai Kamera
                </button>
                <button class="btn btn-primary" id="btnScan" onclick="startScan()" disabled>
                    <i class="fas fa-face-smile"></i> Scan Wajah
                </button>
                <button class="btn btn-success btn-block" id="btnSimpan" onclick="simpanWajah()" disabled style="margin-top:10px;">
                    <i class="fas fa-floppy-disk"></i> Simpan Data Wajah
                </button>
            </div>

        </div>
    </div>

    {{-- Info Karyawan --}}
    <div class="profile-header">
        @if($karyawan && $karyawan->foto)
            <img src="{{ asset('storage/' . $karyawan->foto) }}" alt="Foto Profil" class="profile-avatar">
        @else
            <div class="profile-avatar">
                <i class="fas fa-user"></i>
            </div>
        @endif
        <div class="profile-info">
            <h2>{{ $karyawan->nama ?? Auth::user()->name }} <span style="font-family:monospace;font-size:11px;background:#dbeafe;color:#ea580c;padding:2px 8px;border-radius:5px;display:inline-block;margin-left:6px;vertical-align:middle;">{{ $karyawan->nik ?? '-' }}</span></h2>
            <p>{{ $karyawan->jabatan ?? '-' }} • {{ $karyawan->divisi ?? '-' }}</p>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/@vladmandic/face-api/dist/face-api.js"></script>
<script>
let stream = null, faceApiLoaded = false;
let capturedDescriptor = null, capturedPhoto = null;
const sudahDaftar = {{ $karyawan && $karyawan->face_descriptors ? 'true' : 'false' }};

async function startCamera() {
    if (sudahDaftar) {
        showAlert('Wajah kamu sudah terdaftar. Tidak bisa mendaftar ulang.', 'error');
        return;
    }
    try {
        stream = await navigator.mediaDevices.getUserMedia({ video: { width: 640, height: 480, facingMode: 'user' } });
        document.getElementById('video').srcObject = stream;
        document.getElementById('btnStart').disabled = true;
        setStatus('Memuat model AI...', 'active');
        setStep(1, 'done'); setStep(2, 'active');
        await loadFaceAPI();
        if (faceApiLoaded) {
            document.getElementById('btnScan').disabled = false;
            setStatus('Kamera aktif. Klik "Scan Wajah" untuk mendaftar.', 'active');
        } else {
            // Error status was already set inside loadFaceAPI catch block
            document.getElementById('btnStart').disabled = false;
        }
    } catch(e) {
        setStatus('Gagal akses kamera: ' + e.message, 'error');
        document.getElementById('btnStart').disabled = false;
    }
}

async function loadFaceAPI() {
    const MODEL_URL = 'https://unpkg.com/@vladmandic/face-api@1.7.2/model';
    try {
        const loadModels = Promise.all([
            faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
            faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
            faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
        ]);
        const timeout = new Promise((_, reject) => setTimeout(() => reject(new Error('timeout')), 15000));
        await Promise.race([loadModels, timeout]);
        faceApiLoaded = true;
    } catch(e) {
        faceApiLoaded = false;
        setStatus('Gagal memuat model AI. Periksa koneksi internet.', 'error');
        showAlert('Gagal mengunduh file model AI. Coba gunakan koneksi lain.', 'error');
    }
}

async function startScan() {
    document.getElementById('scanOverlay').classList.add('active');
    document.getElementById('btnScan').disabled = true;
    document.getElementById('btnScan').innerHTML = '<div class="spinner"></div> Scanning...';
    ['TL','TR','BL','BR'].forEach(c => document.getElementById('cc'+c).classList.add('scanning'));
    setStatus('Mendeteksi wajah...', 'active');

    let attempts = 0;
    const id = setInterval(async () => {
        if (++attempts > 40) {
            clearInterval(id);
            stopScanUI();
            setStatus('Wajah tidak terdeteksi. Coba lagi.', 'error');
            return;
        }

        const video = document.getElementById('video');
        const opts = new faceapi.TinyFaceDetectorOptions({ inputSize: 320, scoreThreshold: 0.5 });
        const det = await faceapi.detectSingleFace(video, opts).withFaceLandmarks(true).withFaceDescriptor();

        if (!det) return;

        const conf = Math.round(det.detection.score * 100);
        if (conf < 40) { setStatus(`Kurang jelas (${conf}%). Dekatkan wajah...`, 'active'); return; }

        clearInterval(id);
        capturedDescriptor = Array.from(det.descriptor);

        // Ambil foto
        const canvas = document.getElementById('canvas');
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
        const ctx = canvas.getContext('2d');
        ctx.translate(canvas.width, 0);
        ctx.scale(-1, 1);
        ctx.drawImage(video, 0, 0);
        capturedPhoto = canvas.toDataURL('image/jpeg', 0.8);

        // Preview foto
        const preview = document.querySelector('.profile-avatar');
        if (preview) {
            preview.innerHTML = `<img src="${capturedPhoto}" style="width:100%;height:100%;object-fit:cover;border-radius:50%;">`;
            preview.style.border = '3px solid #059669';
        }

        // Show success
        const overlay = document.getElementById('successOverlay');
        overlay.classList.add('show');
        setTimeout(() => overlay.classList.remove('show'), 2000);

        stopScanUI();
        setStep(2, 'done'); setStep(3, 'active');
        setStatus(`✓ Wajah terdeteksi dengan akurasi ${conf}%!`, 'matched');
        document.getElementById('btnSimpan').disabled = false;
    }, 300);
}

async function simpanWajah() {
    if (!capturedDescriptor || !capturedPhoto) return;
    const btn = document.getElementById('btnSimpan');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Menyimpan...';

    try {
        const res = await fetch('{{ route("karyawan.daftar-wajah.simpan") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({
                descriptor: JSON.stringify(capturedDescriptor),
                photo: capturedPhoto
            })
        });
        const data = await res.json();
        if (data.success) {
            showAlert('Wajah berhasil didaftarkan! Kamu sekarang bisa melakukan presensi.', 'success');
            setStep(3, 'done');
            setTimeout(() => window.location.reload(), 2000);
        } else {
            showAlert(data.message || 'Gagal menyimpan.', 'error');
        }
    } catch(e) {
        showAlert('Terjadi kesalahan: ' + e.message, 'error');
    }

    btn.innerHTML = '<i class="fas fa-floppy-disk"></i> Simpan Data Wajah';
    btn.disabled = false;
}

function stopScanUI() {
    document.getElementById('scanOverlay').classList.remove('active');
    document.getElementById('btnScan').disabled = false;
    document.getElementById('btnScan').innerHTML = '<i class="fas fa-face-smile"></i> Scan Ulang';
    ['TL','TR','BL','BR'].forEach(c => document.getElementById('cc'+c).classList.remove('scanning'));
}

function setStatus(msg, type) {
    const el = document.getElementById('scanStatus');
    const icons = { active: 'circle-notch fa-spin', matched: 'circle-check', error: 'triangle-exclamation' };
    const colors = { active: '#ea580c', matched: '#059669', error: '#e11d48' };
    el.innerHTML = `<i class="fas fa-${icons[type]||'circle-dot'}" style="color:${colors[type]||'#64748b'}"></i> ${msg}`;
}

function setStep(n, state) {
    const el = document.getElementById('step' + n);
    el.className = 'step ' + state;
}

function showAlert(msg, type) {
    const box = document.getElementById('alertBox');
    box.innerHTML = `<div class="alert alert-${type}"><i class="fas fa-${type==='success'?'circle-check':'circle-xmark'}"></i> ${msg}</div>`;
    setTimeout(() => box.innerHTML = '', 5000);
}
</script>
</body>
</html>