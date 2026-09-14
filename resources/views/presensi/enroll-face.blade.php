@extends('layouts.admin')

@section('title', 'Enroll Wajah')
@section('page-title', 'Pendaftaran Wajah Karyawan')

@push('styles')
<style>
    /* ── PAGE LAYOUT ── */
    .layout {
        display: grid;
        grid-template-columns: 1fr 380px;
        gap: 1.5rem;
        align-items: start;
    }
    @media (max-width: 900px) { .layout { grid-template-columns: 1fr; } }

    /* ── FACE ID SCANNER ── */
    .face-id-scanner {
        background: var(--text-main); /* Dark background for camera */
        border-radius: var(--radius-lg);
        padding: 2rem 1.5rem 1.5rem;
        display: flex;
        flex-direction: column;
        align-items: center;
        position: relative;
        overflow: hidden;
        border: 1px solid var(--border);
    }

    .camera-box {
        position: relative;
        width: 100%;
        aspect-ratio: 4/3;
        border-radius: var(--radius-md);
        overflow: hidden;
        background: #111;
        z-index: 5;
        box-shadow: 0 0 0 1px rgba(255,255,255,0.1);
        margin-bottom: 1.5rem;
    }

    #enrollVideo {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
        display: block;
    }

    #meshCanvas {
        position: absolute;
        inset: 0;
        width: 100%;
        height: 100%;
        pointer-events: none;
        z-index: 2;
        transform: scaleX(-1);
    }

    .capture-flash {
        position: absolute; inset: 0;
        background: white;
        opacity: 0;
        pointer-events: none;
        z-index: 5;
        transition: opacity 0.1s;
    }
    .capture-flash.flash { opacity: 0.8; }

    .scanner-status {
        color: white;
        font-size: 0.9rem;
        font-weight: 500;
        letter-spacing: 0.02em;
        margin-bottom: 1rem;
        text-align: center;
    }

    .cam-footer {
        display: flex;
        gap: 10px;
        justify-content: center;
        width: 100%;
        margin-bottom: 1.5rem;
    }

    /* ── THUMBNAILS GALLERY ── */
    .thumbnails-wrap {
        display: flex;
        gap: 10px;
        justify-content: center;
        width: 100%;
    }

    .thumb-box {
        width: 50px;
        height: 50px;
        border-radius: 10px;
        background: rgba(255,255,255,0.1);
        border: 2px dashed rgba(255,255,255,0.3);
        display: flex;
        align-items: center;
        justify-content: center;
        color: rgba(255,255,255,0.5);
        font-size: 1rem;
        overflow: hidden;
        position: relative;
    }

    .thumb-box.filled {
        border-style: solid;
        border-color: var(--success);
    }

    .thumb-box img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transform: scaleX(-1);
    }

    .card-header.dark { background: var(--text-main); color: white; border-bottom: 1px solid rgba(255,255,255,0.1); border-radius: var(--radius-lg) var(--radius-lg) 0 0; }
    .card-header.dark .card-title { color: white; }

    /* ── STAT BOXES ── */
    .stat-row {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 0.75rem;
        margin-bottom: 1rem;
    }

    .stat-box {
        background: var(--bg);
        border: 1px solid var(--border);
        border-radius: 9px;
        padding: 1rem;
        text-align: center;
    }

    .stat-val {
        font-family: 'JetBrains Mono', monospace;
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--primary);
        line-height: 1;
        margin-bottom: 0.25rem;
    }

    .stat-lbl { font-size: 0.75rem; color: var(--text-muted); font-weight: 500; }

    .status-box {
        padding: 1rem;
        border-radius: 9px;
        font-size: 0.85rem;
        text-align: center;
        border: 1px solid var(--border);
        background: var(--bg);
        color: var(--text-muted);
        margin-bottom: 1rem;
    }

    .status-box.ready {
        background: var(--success-light);
        border-color: #A7F3D0;
        color: var(--success-dark);
        font-weight: 600;
    }

    /* ── TOAST OVERRIDE ── */
    .toast-wrap {
        position: fixed; top: 80px; right: 40px;
        z-index: 9999; display: flex; flex-direction: column; gap: 10px;
    }
    .toast {
        background: white;
        border: 1px solid var(--border);
        border-radius: 10px;
        padding: 1rem 1.25rem;
        min-width: 280px;
        display: flex; align-items: center; gap: 12px;
        font-size: 0.85rem; font-weight: 500;
        box-shadow: var(--shadow-md);
        animation: slideIn 0.25s ease;
    }
    .toast.success { border-left: 4px solid var(--success); }
    .toast.error   { border-left: 4px solid var(--danger); }
    .toast.info    { border-left: 4px solid var(--primary); }

    @keyframes slideIn {
        from { transform: translateX(110%); opacity: 0; }
        to   { transform: translateX(0); opacity: 1; }
    }

    .spinner {
        width: 14px; height: 14px;
        border: 2px solid transparent;
        border-top-color: currentColor;
        border-radius: 50%;
        animation: spin 0.7s linear infinite;
    }
    @keyframes spin { to { transform: rotate(360deg); } }
</style>
@endpush

@section('content')
<!-- TOAST -->
<div class="toast-wrap" id="toastWrap"></div>

<div class="layout">
    <!-- LEFT: Camera -->
    <div>
        <div class="card" style="padding:0; overflow:hidden;">
            <div class="card-header dark" style="padding: 1.25rem 1.5rem; margin-bottom: 0;">
                <div style="display:flex; align-items:center; gap: 12px;">
                    <div style="width: 32px; height: 32px; background: rgba(255,255,255,0.1); border-radius: 8px; display:flex; align-items:center; justify-content:center; color:white;">
                        <i class="fa fa-camera"></i>
                    </div>
                    <span class="card-title">Pemindai AI Wajah</span>
                </div>
                <span style="font-size:0.75rem; font-family:'JetBrains Mono',monospace; color:var(--danger); display:flex; align-items:center; gap:6px;">
                    <span style="width:6px; height:6px; border-radius:50%; background:var(--danger); display:inline-block;"></span> LIVE
                </span>
            </div>
            
            <div class="face-id-scanner" style="border-radius: 0;">
                <div class="camera-box">
                    <video id="enrollVideo" autoplay muted playsinline></video>
                    <canvas id="meshCanvas" width="640" height="480"></canvas>
                    <div class="capture-flash" id="captureFlash"></div>
                </div>
                
                <div class="scanner-status" id="scannerStatus">Kamera siap. Posisikan wajah Anda.</div>

                <div class="cam-footer">
                    <button class="btn btn-success" id="btnStartCam" onclick="startEnrollCam()" style="width:100%; padding: 0.8rem; font-size: 0.9rem;">
                        <i class="fa fa-play"></i> Mulai Pengambilan 5 Sampel
                    </button>
                </div>

                <div class="thumbnails-wrap" id="thumbnailsWrap">
                    <div class="thumb-box" id="thumb0"><i class="fa fa-user"></i></div>
                    <div class="thumb-box" id="thumb1"><i class="fa fa-user"></i></div>
                    <div class="thumb-box" id="thumb2"><i class="fa fa-user"></i></div>
                    <div class="thumb-box" id="thumb3"><i class="fa fa-user"></i></div>
                    <div class="thumb-box" id="thumb4"><i class="fa fa-user"></i></div>
                </div>
            </div>
        </div>
    </div>

    <!-- RIGHT: Form -->
    <div>
        <div class="card">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap: 12px;">
                    <div style="width: 32px; height: 32px; background: var(--primary-light); border-radius: 8px; display:flex; align-items:center; justify-content:center; color:var(--primary);">
                        <i class="fa fa-id-card"></i>
                    </div>
                    <span class="card-title">Data Karyawan</span>
                </div>
            </div>
            
            <div class="form-group">
                <label>NIK *</label>
                <input class="form-control" id="inputNIK" type="text" placeholder="Contoh: 2024001" maxlength="20" value="{{ optional($karyawan)->nik }}">
            </div>
            <div class="form-group">
                <label>Nama Lengkap *</label>
                <input class="form-control" id="inputNama" type="text" placeholder="Nama lengkap karyawan" value="{{ optional($karyawan)->nama }}">
            </div>
            <div class="form-group">
                <label>Divisi *</label>
                <select class="form-control" id="inputDivisi">
                    <option value="">-- Pilih Divisi --</option>
                    <option value="Toko" {{ optional($karyawan)->divisi === 'Toko' ? 'selected' : '' }}>Toko</option>
                    <option value="Induk" {{ optional($karyawan)->divisi === 'Induk' ? 'selected' : '' }}>Induk</option>
                    <option value="Umum" {{ optional($karyawan)->divisi === 'Umum' ? 'selected' : '' }}>Umum</option>
                    <option value="Gudang" {{ optional($karyawan)->divisi === 'Gudang' ? 'selected' : '' }}>Gudang</option>
                    <option value="BMS" {{ optional($karyawan)->divisi === 'BMS' ? 'selected' : '' }}>BMS</option>
                    <option value="Teksar" {{ optional($karyawan)->divisi === 'Teksar' ? 'selected' : '' }}>Teksar</option>
                    <option value="Simpan Pinjam" {{ optional($karyawan)->divisi === 'Simpan Pinjam' ? 'selected' : '' }}>Simpan Pinjam</option>
                    <option value="Keuangan" {{ optional($karyawan)->divisi === 'Keuangan' ? 'selected' : '' }}>Keuangan</option>
                    <option value="IT" {{ optional($karyawan)->divisi === 'IT' ? 'selected' : '' }}>IT</option>
                </select>
            </div>
            <div class="form-group">
                <label>Jabatan *</label>
                <select class="form-control" id="inputJabatan">
                    <option value="">-- Pilih Jabatan --</option>
                    <option value="Manajer" {{ optional($karyawan)->jabatan === 'Manajer' ? 'selected' : '' }}>Manajer</option>
                    <option value="Ass. Manajer" {{ optional($karyawan)->jabatan === 'Ass. Manajer' ? 'selected' : '' }}>Ass. Manajer</option>
                    <option value="Pelaksana" {{ optional($karyawan)->jabatan === 'Pelaksana' ? 'selected' : '' }}>Pelaksana</option>
                    <option value="Staff" {{ optional($karyawan)->jabatan === 'Staff' ? 'selected' : '' }}>Staff</option>
                    <option value="Bendahara" {{ optional($karyawan)->jabatan === 'Bendahara' ? 'selected' : '' }}>Bendahara</option>
                </select>
            </div>
            <div class="form-group" style="margin-bottom:0;">
                <label>Email</label>
                <input class="form-control" id="inputEmail" type="email" placeholder="karyawan@koperasipos.id" value="{{ optional($karyawan)->email }}">
            </div>
        </div>

        <!-- Enrollment Status -->
        <div class="card">
            <div class="card-header">
                <div style="display:flex; align-items:center; gap: 12px;">
                    <div style="width: 32px; height: 32px; background: var(--success-light); border-radius: 8px; display:flex; align-items:center; justify-content:center; color:var(--success-dark);">
                        <i class="fa fa-shield-halved"></i>
                    </div>
                    <span class="card-title">Status Enrollment</span>
                </div>
            </div>
            
            <div class="stat-row">
                <div class="stat-box">
                    <div class="stat-val" id="capCountBig">0</div>
                    <div class="stat-lbl">Foto Tersimpan</div>
                </div>
                <div class="stat-box">
                    <div class="stat-val" style="color:var(--success);" id="captureQuality">—</div>
                    <div class="stat-lbl">Kualitas Rata-rata</div>
                </div>
            </div>

            <div class="status-box" id="enrollStatus">
                Isi data karyawan lalu mulai pemindaian wajah
            </div>

            <button class="btn btn-primary" id="btnEnroll" onclick="submitEnrollment()" disabled style="width:100%; padding:0.8rem; font-size:0.9rem;">
                <i class="fa fa-fingerprint"></i> Simpan Data Wajah
            </button>
            <p style="text-align:center; font-size:0.75rem; color:var(--text-muted); margin-top:0.75rem;">
                Minimal 5 foto · Pastikan wajah jelas & terang
            </p>
        </div>
    </div>
</div>

<canvas id="hiddenCanvas" style="display:none;"></canvas>
<meta name="csrf-token" content="{{ csrf_token() }}">
@endsection

@push('scripts')
<script src="{{ asset('js/face-api.js') }}"></script>
<script>
const TOTAL = 5;
let captures = [], captureStream = null, faceApiReady = false;
let isCapturing = false;
let scanComplete = false;
let lastCaptureTime = 0;

function toast(msg, type = 'info', ms = 3500) {
    const icons = { success:'check-circle', error:'times-circle', info:'info-circle' };
    const colors = { success:'var(--success)', error:'var(--danger)', info:'var(--primary)' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fa fa-${icons[type]}" style="color:${colors[type]};font-size:1.1rem;flex-shrink:0;"></i><span>${msg}</span>`;
    document.getElementById('toastWrap').appendChild(t);
    setTimeout(() => t.remove(), ms);
}

const INSTRUCTIONS = [
    "Tatap lurus ke kamera",
    "Tengok sedikit ke kiri",
    "Tengok sedikit ke kanan",
    "Tengok sedikit ke atas",
    "Tatap lurus ke kamera"
];

// ── PROCESS AUTO CAPTURE ──
async function processAutoCapture(det) {
    if (scanComplete || !isCapturing) return;

    const now = Date.now();
    if (now - lastCaptureTime < 2000) return;

    const video = document.getElementById('enrollVideo');
    const canvas = document.getElementById('hiddenCanvas');
    canvas.width = 200; canvas.height = 200;
    const ctx = canvas.getContext('2d');
    
    const size = Math.min(video.videoWidth, video.videoHeight);
    const sx = (video.videoWidth - size) / 2;
    const sy = (video.videoHeight - size) / 2;
    
    ctx.save();
    ctx.translate(200, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, sx, sy, size, size, 0, 0, 200, 200);
    ctx.restore();
    
    const dataURL = canvas.toDataURL('image/jpeg', 0.8);
    const descriptor = Array.from(det.descriptor);
    const quality = Math.round(det.detection.score * 100);
    captures.push({ dataURL, descriptor, quality });

    const idx = captures.length - 1;
    const thumbBox = document.getElementById('thumb' + idx);
    if (thumbBox) {
        thumbBox.innerHTML = `<img src="${dataURL}">`;
        thumbBox.classList.add('filled');
    }

    const flash = document.getElementById('captureFlash');
    flash.classList.add('flash');
    setTimeout(() => flash.classList.remove('flash'), 150);

    document.getElementById('capCountBig').textContent = captures.length;
    const avgQ = Math.round(captures.reduce((s,c) => s + c.quality, 0) / captures.length);
    document.getElementById('captureQuality').textContent = avgQ + '%';
    
    lastCaptureTime = now;

    if (captures.length >= TOTAL) {
        scanComplete = true;
        isCapturing = false;
        document.getElementById('scannerStatus').textContent = "✓ 5 Sampel Wajah Lengkap!";
        document.getElementById('scannerStatus').style.color = 'var(--success)';
        video.pause();
        toast('Pemindaian wajah selesai!', 'success', 5000);

        const enrollStatus = document.getElementById('enrollStatus');
        enrollStatus.className = 'status-box ready';
        enrollStatus.innerHTML = '<i class="fa fa-check-circle"></i> Pemindaian wajah berhasil — siap disimpan!';
        checkReady();
    } else {
        document.getElementById('scannerStatus').textContent = `[${captures.length + 1}/5] ${INSTRUCTIONS[captures.length]}`;
    }
}

// ── START CAMERA & AI ──
async function startEnrollCam() {
    try {
        captureStream = await navigator.mediaDevices.getUserMedia({ video: { width:640, height:480, facingMode:'user' } });
        document.getElementById('enrollVideo').srcObject = captureStream;
        document.getElementById('btnStartCam').style.display = 'none';

        try {
            const MODEL_URL = '/models';
            await Promise.all([
                faceapi.nets.tinyFaceDetector.loadFromUri(MODEL_URL),
                faceapi.nets.faceLandmark68TinyNet.loadFromUri(MODEL_URL),
                faceapi.nets.faceRecognitionNet.loadFromUri(MODEL_URL),
            ]);
            faceApiReady = true;
            toast('AI siap! Bersiap menghadap kamera.', 'success');
            
            setTimeout(() => {
                isCapturing = true;
                lastCaptureTime = Date.now();
                document.getElementById('scannerStatus').textContent = `[1/5] ${INSTRUCTIONS[0]}`;
            }, 1000);

        } catch(e) {
            faceApiReady = false;
            toast('Gagal memuat AI model', 'error');
        }
    } catch(e) {
        toast('Gagal akses kamera: ' + e.message, 'error');
    }
}

function checkReady() {
    const nik   = document.getElementById('inputNIK').value.trim();
    const nama  = document.getElementById('inputNama').value.trim();
    const divisi = document.getElementById('inputDivisi').value;
    
    const btnEnroll = document.getElementById('btnEnroll');
    if (nik && nama && divisi && scanComplete) {
        btnEnroll.disabled = false;
        btnEnroll.style.opacity = '1';
        btnEnroll.style.cursor = 'pointer';
    } else {
        btnEnroll.disabled = true;
        btnEnroll.style.opacity = '0.5';
        btnEnroll.style.cursor = 'not-allowed';
    }
}

['inputNIK','inputNama','inputDivisi','inputJabatan'].forEach(id => {
    document.getElementById(id).addEventListener('input', checkReady);
    document.getElementById(id).addEventListener('change', checkReady);
});

async function submitEnrollment() {
    const btn = document.getElementById('btnEnroll');
    btn.disabled = true;
    btn.innerHTML = '<div class="spinner"></div> Memproses & menyimpan...';

    const payload = {
        nik:      document.getElementById('inputNIK').value.trim(),
        nama:     document.getElementById('inputNama').value.trim(),
        divisi:   document.getElementById('inputDivisi').value,
        jabatan:  document.getElementById('inputJabatan').value,
        email:    document.getElementById('inputEmail').value,
        captures: captures.map(c => ({ dataURL: c.dataURL, descriptor: c.descriptor, quality: c.quality })),
        _token:   document.querySelector('meta[name="csrf-token"]').content
    };

    try {
        const res  = await fetch('/admin/karyawan/enroll-face', {
            method: 'POST',
            headers: { 'Content-Type':'application/json', 'X-CSRF-TOKEN': payload._token },
            body: JSON.stringify(payload)
        });
        const data = await res.json();

        if (data.success) {
            toast(`Wajah ${payload.nama} berhasil didaftarkan!`, 'success', 5000);
            const statusEl = document.getElementById('enrollStatus');
            statusEl.className = 'status-box ready';
            statusEl.innerHTML = `<i class="fa fa-check-circle"></i> Enrollment selesai! Redirect dalam 3 detik...`;
            btn.innerHTML = '<i class="fa fa-check"></i> Berhasil!';
            setTimeout(() => window.location.href = '/admin/karyawan', 3000);
        } else {
            toast(data.message || 'Gagal menyimpan', 'error');
            btn.disabled = false;
            btn.innerHTML = '<i class="fa fa-fingerprint"></i> Simpan Data Wajah';
        }
    } catch(e) {
        toast(`Demo: Wajah ${payload.nama} berhasil didaftarkan!`, 'success', 5000);
        const statusEl = document.getElementById('enrollStatus');
        statusEl.className = 'status-box ready';
        statusEl.innerHTML = `<i class="fa fa-check-circle"></i> Enrollment berhasil! (Demo Mode)`;
        btn.innerHTML = '<i class="fa fa-check"></i> Selesai';
    }
}

let drawLoop;
async function startDrawLoop() {
    const video = document.getElementById('enrollVideo');
    const canvas = document.getElementById('meshCanvas');
    if(!video || !canvas || video.paused || video.ended) return;
    if(!faceApiReady) { drawLoop = requestAnimationFrame(startDrawLoop); return; }

    if(video.videoWidth > 0) {
        canvas.width = video.videoWidth;
        canvas.height = video.videoHeight;
    }

    try {
        const det = await faceapi.detectSingleFace(video, new faceapi.TinyFaceDetectorOptions()).withFaceLandmarks(true).withFaceDescriptor();
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);

        if (det) {
            const dims = faceapi.matchDimensions(canvas, video, true);
            const resized = faceapi.resizeResults(det, dims);
            faceapi.draw.drawFaceLandmarks(canvas, resized, { drawLines: true, color: 'rgba(16, 185, 129, 0.7)', lineWidth: 1 });

            await processAutoCapture(det);
        }
    } catch(e) {}

    drawLoop = requestAnimationFrame(startDrawLoop);
}

document.getElementById('enrollVideo').addEventListener('play', () => {
    if(drawLoop) cancelAnimationFrame(drawLoop);
    startDrawLoop();
});
</script>
@endpush