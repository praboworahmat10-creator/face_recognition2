/**
 * KOPPOSINDO FaceID — Halaman Presensi
 * Config via window.PRESENSI_CONFIG (diset dari Blade)
 */
(function () {
'use strict';

const CFG = window.PRESENSI_CONFIG || {};
const KANTOR_LAT = CFG.kantorLat ?? 0;
const KANTOR_LNG = CFG.kantorLng ?? 0;
const MAX_RADIUS = CFG.maxRadius ?? 30;
const NIK_LOGIN = CFG.nikLogin ?? '';
const DETECTION_MIN_CONF = CFG.detectionMinConfidence ?? 60;

let stream = null, faceApiLoaded = false;
const engine = new FaceRecognitionEngine('/models', {
    matchThreshold: CFG.matchThreshold ?? 0.55,
    matchMargin: CFG.matchMargin ?? 0.08,
});
let currentStudent = null, faceVerified = false, locationVerified = false;
let userLat = null, userLng = null;
let frameCount = 0, lastFPSTime = Date.now(), simCounter = 0;
let holdMatchStart = 0;


function speakText(text) {
    if ('speechSynthesis' in window) {
        window.speechSynthesis.cancel();
        const msg = new SpeechSynthesisUtterance();
        msg.text = text;
        msg.lang = 'id-ID';
        msg.rate = 0.9;
        msg.pitch = 1.0;
        window.speechSynthesis.speak(msg);
    }
}

// Clock & date
document.getElementById('dateLabel').textContent =
    new Date().toLocaleDateString('id-ID', { weekday:'long', day:'numeric', month:'long', year:'numeric' });
function updateClock() {
    document.getElementById('liveClock').textContent =
        new Date().toLocaleTimeString('id-ID', { hour12:false });
}
setInterval(updateClock, 1000); updateClock();

// Toast
function showToast(msg, type='info', ms=3500) {
    const icons = { success:'circle-check', error:'circle-xmark', info:'circle-info', warning:'triangle-exclamation' };
    const colors = { success:'var(--emerald)', error:'var(--rose)', info:'var(--blue)', warning:'var(--amber)' };
    const t = document.createElement('div');
    t.className = `toast ${type}`;
    t.innerHTML = `<i class="fas fa-${icons[type]}" style="color:${colors[type]};font-size:15px;"></i><span>${msg}</span>`;
    document.getElementById('toastWrap').appendChild(t);
    setTimeout(() => t.remove(), ms);
}

// Modal
function openModal(type) {
    document.getElementById('modal' + type.charAt(0).toUpperCase() + type.slice(1)).classList.add('show');
}
function closeModal(type) {
    document.getElementById('modal' + type.charAt(0).toUpperCase() + type.slice(1)).classList.remove('show');
}
function showFileName(input, targetId) {
    document.getElementById(targetId).textContent = input.files[0]?.name || '';
}
function submitForm(formId) {
    if (formId === 'formSakit') {
        const fileSakit = document.getElementById('fileSakit');
        if (!fileSakit.files || fileSakit.files.length === 0) {
            const existing = document.getElementById('errorSakit');
            if (existing) existing.remove();
            const err = document.createElement('div');
            err.id = 'errorSakit';
            err.style.cssText = 'background:rgba(225,29,72,0.1);border:1px solid rgba(225,29,72,0.3);color:#fb7185;padding:10px 14px;border-radius:9px;font-size:13px;display:flex;align-items:center;gap:8px;margin-top:8px;';
            err.innerHTML = '<i class="fas fa-circle-exclamation"></i> Surat dokter / bukti sakit wajib dilampirkan!';
            fileSakit.closest('.form-group').appendChild(err);
            fileSakit.closest('label').style.borderColor = 'rgba(225,29,72,0.5)';
            fileSakit.closest('label').style.background = 'rgba(225,29,72,0.05)';
            setTimeout(() => {
                err?.remove();
                fileSakit.closest('label').style.borderColor = '';
                fileSakit.closest('label').style.background = '';
            }, 4000);
            return;
        }
    }
    document.getElementById(formId).submit();
}

// Close modal on overlay click
document.querySelectorAll('.modal-overlay').forEach(overlay => {
    overlay.addEventListener('click', e => {
        if (e.target === overlay) overlay.classList.remove('show');
    });
});

// Status
function setStatus(msg, type='') {
    const el = document.getElementById('scanStatus');
    if (el) {
        const iconMap = { active:'circle-notch fa-spin', matched:'circle-check', error:'triangle-exclamation', '':'circle-dot' };
        el.className = `scan-status ${type}`;
        el.innerHTML = `<i class="fas fa-${iconMap[type]||'circle-dot'}"></i><span>${msg}</span>`;
    }
    
    // Update new alert panel for prominent instructions
    const alertEl = document.getElementById('scanStatusAlert');
    const textEl = document.getElementById('scanStatusText');
    if (alertEl && textEl) {
        alertEl.style.display = 'flex';
        alertEl.className = `scan-status-alert ${type}`;
        textEl.textContent = msg;
    }
}

// Camera
async function startCamera() {
    if (!window.isSecureContext && !['localhost', '127.0.0.1'].includes(location.hostname)) {
        const msg = 'Kamera di HP memerlukan HTTPS. Buka via https://IP-ANDA:8443 (jalankan: npm run serve:mobile)';
        setStatus('Perlu HTTPS untuk kamera', 'error');
        showToast(msg, 'error', 12000);
        document.getElementById('btnScanMasuk').disabled = true;
        document.getElementById('btnScanPulang').disabled = true;
        document.getElementById('btnScanMasuk').innerHTML = 'Perlu HTTPS';
        document.getElementById('btnScanPulang').innerHTML = 'Perlu HTTPS';
        return;
    }

    try {
        document.getElementById('btnScanMasuk').disabled = true;
        document.getElementById('btnScanPulang').disabled = true;
        document.getElementById('btnScanMasuk').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyiapkan AI...';
        document.getElementById('btnScanPulang').innerHTML = '<i class="fas fa-spinner fa-spin"></i>';
        
        stream = await navigator.mediaDevices.getUserMedia({ video:{ width:640, height:480, facingMode:'user' } });
        document.getElementById('video').srcObject = stream;
        setStatus('Memuat model AI...', 'active');
        
        // Auto fetch location
        getLocation();

        await loadFaceAPI();
        
        if (faceApiLoaded) {
            document.getElementById('btnScanMasuk').disabled = false;
            document.getElementById('btnScanPulang').disabled = false;
            document.getElementById('btnScanMasuk').innerHTML = 'ABSEN MASUK';
            document.getElementById('btnScanPulang').innerHTML = 'ABSEN PULANG';
            setStatus('Siap. Pilih Absen Masuk atau Pulang', 'active');
            showToast('Kamera aktif, model AI siap', 'success');
        }
    } catch(e) {
        setStatus('Gagal akses kamera', 'error');
        showToast('Izin kamera ditolak: ' + e.message, 'error');
    }
}

async function loadFaceAPI() {
    try {
        console.log("Loading Face API models...");
        await engine.initialize();
        console.log("Models loaded. Fetching descriptors...");
        await loadStudentDescriptors();
        faceApiLoaded = true;
        console.log("Face API fully loaded.");
    } catch(e) {
        faceApiLoaded = false;
        console.error("Failed to load Face API or descriptors:", e);
        // Tampilkan error ke layar secara paksa agar terbaca di screenshot
        document.getElementById('camLocStatus').innerHTML = `<i class="fas fa-exclamation-triangle" style="color:var(--danger);"></i> <span style="color:var(--danger);font-size:12px;">Error: ${e.message || String(e)}</span>`;
        alert("Gagal memuat AI: " + (e.message || String(e)));
        showToast('Gagal memuat AI: ' + (e.message || String(e)), 'error', 10000);
    }
}

async function loadStudentDescriptors() {
    try {
        const res = await fetch('/api/karyawan/face-descriptors', {
            headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
        });
        if (res.ok) {
            const text = await res.text();
            try {
                const data = JSON.parse(text);
                let targetData = data;
                if (typeof NIK_LOGIN !== 'undefined' && NIK_LOGIN !== '') {
                    targetData = data.filter(s => s.nik === NIK_LOGIN);
                }
                if (targetData.length > 0) {
                    engine.setRegisteredFaces(targetData);
                } else {
                    showToast('Wajah Anda belum terdaftar di sistem!', 'warning');
                }
            } catch (jsonErr) {
                throw new Error("Gagal parsing JSON dari server. Pastikan rute API tidak ter-redirect (misal ke halaman login).");
            }
        } else {
            throw new Error(`Server API merespon dengan status: ${res.status}`);
        }
    } catch(e) {
        console.error("loadStudentDescriptors Error:", e);
        throw e; // Lemparkan error ke loadFaceAPI
    }
}

let scanIntent = '';

async function startScan(jenis) {
    if (faceVerified) {
        faceVerified = false; // Reset if scanning again
    }
    if (engine && engine.isReady) {
        engine.resetLiveness();
        document.getElementById('metricLiveness').textContent = '—';
    }
    scanIntent = jenis;
    document.getElementById('scanOverlay').classList.add('active');
    document.getElementById('btnScanMasuk').disabled = true;
    document.getElementById('btnScanPulang').disabled = true;
    
    if (jenis === 'masuk') {
        document.getElementById('btnScanMasuk').innerHTML = '<div class="spinner"></div> Scanning...';
    } else {
        document.getElementById('btnScanPulang').innerHTML = '<div class="spinner"></div> Scanning...';
    }
    setStatus('Mendeteksi wajah...', 'active');
    ['TL','TR','BL','BR'].forEach(c => document.getElementById('cc'+c).classList.add('scanning'));

    let attempts = 0;
    const id = setInterval(async () => {
        if (++attempts > 60) { // Timeout dinaikkan ke 18 detik (60 * 300ms) untuk memberi waktu user
            clearInterval(id);
            stopScanUI();
            setStatus('Waktu habis! Anda belum mengedipkan mata.', 'error');
            showToast('Sesi pemindaian berakhir (Waktu habis)', 'error');
            return;
        }
        await detectAndRecognize(id);
    }, 300);
}

async function detectAndRecognize(intervalId) {
    const video = document.getElementById('video');
    frameCount++;
    const now = Date.now();
    if (now - lastFPSTime >= 1000) {
        document.getElementById('metricFPS').textContent = frameCount + ' fps';
        frameCount = 0; lastFPSTime = now;
    }

    if (!faceApiLoaded) return; // Tunggu sampai model siap

    try {
        // Menggunakan Engine untuk mengekstrak fitur CNN
        const detections = await engine.scanFace(video);
        if (!detections.length) { hideFaceBox(); return; }

        const det = detections[0];
        const box = det.detection.box;
        showFaceBox(box, video);
        drawLandmarks(det.landmarks.positions, video);

        const detectConf = Math.round(det.detection.score * 100);

        if (detectConf < DETECTION_MIN_CONF) { setStatus('Kurang jelas, dekatkan wajah...', 'active'); return; }

        let matchedNIK = null;
        let matchAccuracy = 0;

        if (engine.isReady && det.descriptor) {
            // --- LIVENESS DETECTION ---
            const liveness = engine.checkLiveness(det.landmarks.positions, video, box);
            document.getElementById('metricLiveness').textContent = Math.round(liveness.score) + '%';
            
            if (!liveness.passed) {
                setStatus('PERINTAH: ' + liveness.message, 'warning');
                document.getElementById('faceBox').style.borderColor = 'var(--amber)';
                // Tampilkan pesan kedip di wajah langsung agar sangat jelas
                document.getElementById('faceLabel').textContent = liveness.message.toUpperCase();
                document.getElementById('faceLabel').style.background = 'var(--warning)';
                document.getElementById('faceLabel').style.color = '#000';
                holdMatchStart = 0; // Reset hold match timer
                return; // Tunggu sampai liveness lulus baru lanjut matching
            } else {
                document.getElementById('faceLabel').style.background = 'var(--success)';
                document.getElementById('faceLabel').style.color = '#fff';
            }

            // --- MULTI-DESCRIPTOR MATCHING ---
            const match = engine.findBestMatch(det.descriptor);
            matchAccuracy = engine.calculateAccuracy(match.distance);
            updateConfBar(matchAccuracy);

            if (match.passed) {
                matchedNIK = match.id;

                if (NIK_LOGIN && matchedNIK !== NIK_LOGIN) {
                    clearInterval(intervalId);
                    stopScanUI();
                    setStatus('Wajah tidak sesuai dengan akun ini!', 'error');
                    showToast('Wajah bukan milik akun yang login!', 'error');
                    holdMatchStart = 0;
                    return;
                }
            } else if (match.distance <= engine.matchThreshold && match.margin < engine.matchMargin) {
                setStatus('Wajah ambigu — posisikan ulang dan tahan sebentar', 'warning');
                holdMatchStart = 0;
                return;
            } else {
                setStatus('Wajah tidak dikenali (Mirip ' + matchAccuracy + '%)', 'error');
                holdMatchStart = 0;
                document.getElementById('faceBox').style.borderColor = 'var(--rose)';
                document.getElementById('faceLabel').textContent = 'Tidak Cocok';
                return;
            }
        }

        if (matchedNIK) {
            if (holdMatchStart === 0) holdMatchStart = Date.now();
            
            const elapsed = Date.now() - holdMatchStart;
            if (elapsed < 2500) {
                const remain = Math.ceil((2500 - elapsed) / 1000);
                setStatus(`Memproses data wajah... tahan posisi (${remain}s)`, 'active');
                document.getElementById('faceBox').style.borderColor = 'var(--blue)';
                document.getElementById('faceLabel').textContent = 'Memproses...';
            } else {
                clearInterval(intervalId);
                holdMatchStart = 0;
                document.getElementById('faceBox').style.borderColor = 'var(--emerald)';
                try {
                    const res = await fetch(`/api/karyawan/${matchedNIK}`, {
                        headers:{ 'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }
                    });
                    const data = await res.json();
                    onFaceMatched({ nik:data.nik, name:data.nama, divisi:data.divisi||'—', jabatan:data.jabatan||'—', foto:data.foto_url||null }, matchAccuracy);
                } catch(e) {
                    onFaceMatched({ nik:matchedNIK, name:'Karyawan Terdaftar', divisi:'—', jabatan:'—' }, matchAccuracy);
                }
            }
        } else {
            holdMatchStart = 0;
            setStatus('Mencocokkan data wajah...', 'active');
            document.getElementById('faceBox').style.borderColor = 'var(--emerald)';
            document.getElementById('faceLabel').textContent = 'Wajah Terdeteksi';
        }
    } catch(e) {}
}

function simulateFaceDetection(intervalId) {
    clearInterval(intervalId);
    stopScanUI();
    setStatus('Model AI gagal dimuat, silakan muat ulang halaman.', 'error');
    showToast('Face Recognition engine error.', 'error');
}

function showFaceBox(box, video, sim=false) {
    const fb = document.getElementById('faceBox');
    const vw = video.clientWidth, vh = video.clientHeight;
    const sx = sim ? 1 : vw/640, sy = sim ? 1 : vh/480;
    fb.style.left   = (vw - box.x*sx - box.width*sx) + 'px';
    fb.style.top    = (box.y*sy) + 'px';
    fb.style.width  = (box.width*sx) + 'px';
    fb.style.height = (box.height*sy) + 'px';
    fb.classList.add('detected');
    document.getElementById('faceLabel').textContent = 'Wajah Terdeteksi';
}

function hideFaceBox() { 
    document.getElementById('faceBox').classList.remove('detected'); 
    const canvas = document.getElementById('canvas');
    if(canvas) {
        const ctx = canvas.getContext('2d');
        ctx.clearRect(0, 0, canvas.width, canvas.height);
    }
}

function drawLandmarks(positions, video) {
    const canvas = document.getElementById('canvas');
    const vw = video.clientWidth, vh = video.clientHeight;
    if (canvas.width !== vw || canvas.height !== vh) {
        canvas.width = vw;
        canvas.height = vh;
    }
    const ctx = canvas.getContext('2d');
    ctx.clearRect(0, 0, vw, vh);
    
    ctx.fillStyle = '#10b981'; // Emerald green
    ctx.globalAlpha = 0.8;
    
    ctx.beginPath();
    positions.forEach(p => {
        const mx = vw - (p.x * (vw/640));
        const my = p.y * (vh/480);
        ctx.moveTo(mx, my);
        ctx.arc(mx, my, 1.8, 0, 2*Math.PI);
    });
    ctx.fill();
}

function onFaceMatched(student, confidence) {
    const video = document.getElementById('video');
    const canvas = document.createElement('canvas');
    canvas.width = video.videoWidth || 640;
    canvas.height = video.videoHeight || 480;
    
    const ctx = canvas.getContext('2d');
    ctx.translate(canvas.width, 0);
    ctx.scale(-1, 1);
    ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
    
    student.snapshot = canvas.toDataURL('image/jpeg', 0.8);

    currentStudent = student;
    faceVerified = true;
    stopScanUI();
    const overlay = document.getElementById('verifiedOverlay');
    overlay.classList.add('show');
    
    // Tampilkan Identity Card
    const idCard = document.getElementById('identityCard');
    idCard.style.display = 'flex';
    // Trigger reflow untuk animasi
    void idCard.offsetWidth;
    idCard.classList.add('show');

    setTimeout(() => overlay.classList.remove('show'), 2000);
    document.getElementById('identityName').textContent = student.name;
    document.getElementById('identityNIK').textContent  = 'NIK: ' + student.nik;
    document.getElementById('identityDivisi').textContent    = student.divisi || student.prodi || '—';
    document.getElementById('identityJabatan').textContent = student.jabatan || student.fakultas || '—';
    document.getElementById('identityAvatar').classList.add('verified');
    if (student.foto) {
        document.getElementById('identityPhoto').src = student.foto;
        document.getElementById('identityPhoto').style.display = 'block';
        document.querySelector('#identityAvatar i').style.display = 'none';
    }
    updateConfBar(confidence);
    setStatus('✓ Wajah cocok: ' + student.name, 'matched');
    showToast(`Wajah terverifikasi: ${student.name}`, 'success');
    checkReady();
}

function updateConfBar(val) {
    const pct = Math.max(0, Math.min(100, Math.round(val))) + '%';
    document.getElementById('metricConf').textContent = pct;
    const confVal = document.getElementById('confVal');
    const confFill = document.getElementById('confFill');
    if (confVal) confVal.textContent = pct;
    if (confFill) confFill.style.width = pct;
}

function stopScanUI() {
    document.getElementById('scanOverlay').classList.remove('active');
    document.getElementById('btnScanMasuk').disabled = false;
    document.getElementById('btnScanPulang').disabled = false;
    document.getElementById('btnScanMasuk').innerHTML = 'ABSEN MASUK';
    document.getElementById('btnScanPulang').innerHTML = 'ABSEN PULANG';
    ['TL','TR','BL','BR'].forEach(c => document.getElementById('cc'+c).classList.remove('scanning'));
    document.getElementById('identityCard').classList.remove('show');
    setTimeout(() => { document.getElementById('identityCard').style.display = 'none'; }, 300);
}


let locMap = null;
let userMarker = null;

function getLocation() {
    if (!navigator.geolocation) { showToast('Geolokasi tidak didukung', 'error'); return; }
    setLocUI('load', 'Mengambil GPS...', 'Mohon tunggu');
    navigator.geolocation.getCurrentPosition(pos => {
        userLat = pos.coords.latitude;
        userLng = pos.coords.longitude;
        
        // Update camLat and camLng text
        const latEl = document.getElementById('camLat');
        const lngEl = document.getElementById('camLng');
        if(latEl) latEl.textContent = 'Lat: ' + userLat.toFixed(6);
        if(lngEl) lngEl.textContent = 'Lng: ' + userLng.toFixed(6);

        const dist = Math.round(getDistance(userLat, userLng, KANTOR_LAT, KANTOR_LNG));
        
        if (dist <= MAX_RADIUS) {
            locationVerified = true;
            setLocUI('ok', 'Lokasi Valid', `${dist}m dari kantor`);
            showToast(`Lokasi terverifikasi — ${dist}m dari kantor`, 'success');
        } else {
            locationVerified = false;
            setLocUI('err', 'Di Luar Radius', `${dist}m (maks. ${MAX_RADIUS}m)`);
            showToast(`Terlalu jauh dari kantor: ${dist}m`, 'error');
        }
        checkReady();
    }, err => {
        setLocUI('err', 'Gagal Ambil Lokasi', err.message);
        showToast('Gagal mendapatkan GPS', 'error');
    }, { enableHighAccuracy:true, timeout:15000 });
}

function setLocUI(type, title, sub) {
    // Update hidden old elements (required by engine internals)
    const icon = document.getElementById('locIcon');
    const iconMap = { ok:'circle-check', err:'circle-xmark', load:'circle-notch fa-spin', '':'location-crosshairs' };
    if (icon) {
        icon.className = `loc-icon ${type==='ok'?'ok':type==='err'?'err':''}`;
        icon.innerHTML = `<i class="fas fa-${iconMap[type]||'location-crosshairs'}"></i>`;
    }
    const locTitleEl = document.getElementById('locTitle');
    const locSubEl = document.getElementById('locSub');
    if (locTitleEl) locTitleEl.textContent = title;
    if (locSubEl) locSubEl.textContent = sub;

    // Update VISIBLE new camLocStatus panel
    const camStatus = document.getElementById('camLocStatus');
    if (camStatus) {
        if (type === 'ok') {
            camStatus.innerHTML = `<i class="fas fa-check-circle" style="color:var(--success);"></i><span style="color:var(--success);">Dalam Radius (${sub})</span>`;
        } else if (type === 'err') {
            camStatus.innerHTML = `<i class="fas fa-times-circle" style="color:var(--danger);"></i><span style="color:var(--danger);">Di Luar Radius (${sub})</span>`;
        } else {
            camStatus.innerHTML = `<i class="fas fa-spinner fa-spin" style="color:var(--warning);"></i><span style="color:var(--text-muted);">${title}</span>`;
        }
    }
}

function getDistance(lat1, lng1, lat2, lng2) {
    const R = 6371000;
    const dLat=(lat2-lat1)*Math.PI/180, dLng=(lng2-lng1)*Math.PI/180;
    const a=Math.sin(dLat/2)**2+Math.cos(lat1*Math.PI/180)*Math.cos(lat2*Math.PI/180)*Math.sin(dLng/2)**2;
    return R*2*Math.atan2(Math.sqrt(a),Math.sqrt(1-a));
}

function checkReady() {
    if (faceVerified && locationVerified) {
        submitPresensi();
    }
}

async function submitPresensi() {
    if (!faceVerified || !locationVerified || !currentStudent) return;
    
    // UI Feedback
    document.getElementById('autoSubmitCard').style.display = 'block';
    
    const payload = {
        nik: currentStudent.nik, nama: currentStudent.name,
        latitude: userLat, longitude: userLng,
        confidence: document.getElementById('confVal').textContent,
        timestamp: new Date().toISOString(),
        snapshot: currentStudent.snapshot,
        jenis_presensi: scanIntent,
        _token: document.querySelector('meta[name="csrf-token"]').content
    };
    try {
        const res = await fetch('/presensi/simpan', {
            method:'POST',
            headers:{ 'Content-Type':'application/json', 'X-CSRF-TOKEN':payload._token },
            body: JSON.stringify(payload)
        });
        const data = await res.json();
        if (data.success) {
            const msgTts = scanIntent === 'masuk' ? 'Absen Masuk Berhasil' : 'Absen Pulang Berhasil';
            speakText(msgTts + ', ' + currentStudent.name);
            showToast(`${data.message} — ${currentStudent.name}`, 'success', 5000);
            addRecent(currentStudent, data.data.status); // backend now returns 'datang' or 'pulang'
            resetForm();
        } else {
            showToast(data.message || 'Gagal menyimpan', 'error');
        }
    } catch(e) {
        showToast('Terjadi kesalahan koneksi', 'error');
    }
    
    document.getElementById('autoSubmitCard').style.display = 'none';
}

function resetForm() {
    faceVerified = false; currentStudent = null;
    // Biarkan identitas dan metrik tetap tampil di layar
    // document.getElementById('identityName').innerHTML = '<span class="placeholder">Belum terdeteksi</span>';
    // document.getElementById('identityNIK').textContent = 'NIK: —';
    // ['identityDivisi','identityJabatan'].forEach(id => document.getElementById(id).textContent = '—');
    // document.getElementById('identityAvatar').classList.remove('verified');
    // document.getElementById('confFill').style.width = '0%';
    // document.getElementById('confVal').textContent = '0%';
    // ['metricConf','metricLandmarks','metricEyeDist'].forEach(id => document.getElementById(id).textContent = '—');
    
    hideFaceBox();
    const landmarkSvg = document.getElementById('landmarkSvg');
    if (landmarkSvg) landmarkSvg.innerHTML = '';
    
    setStatus('Presensi Berhasil. Siap untuk aktivitas berikutnya.', 'active');
    checkReady();
}

// ── KOPPOSINDO UI LOGIC ──
function toggleSidebar() {
    document.getElementById('sidebar').classList.toggle('active');
    document.getElementById('sidebarOverlay').classList.toggle('show');
}

function closeSidebar() {
    document.getElementById('sidebar').classList.remove('active');
    document.getElementById('sidebarOverlay').classList.remove('show');
}

function setBottomNavActive(view) {
    const bnDash = document.getElementById('bnDashboard');
    const bnPres = document.getElementById('bnPresensi');
    if (!bnDash || !bnPres) return;
    bnDash.classList.toggle('active', view === 'dashboard');
    bnPres.classList.toggle('active', view === 'camera');
}

function switchView(view) {
    const dash = document.getElementById('viewDashboard');
    const cam = document.getElementById('viewKamera');
    const navDash = document.getElementById('navDashboard');
    const navCam = document.getElementById('navPresensi');
    const topbarTitle = document.querySelector('.mobile-topbar h1');
    
    closeSidebar();
    
    if (view === 'camera') {
        dash.style.display = 'none';
        cam.style.display = 'block';
        document.body.classList.add('view-camera');
        document.body.classList.remove('view-dashboard');
        navDash.classList.remove('active');
        navCam.classList.add('active');
        if (topbarTitle) topbarTitle.textContent = 'Presensi';
        setBottomNavActive('camera');
        startCamera();
        getLocation();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    } else {
        dash.style.display = 'block';
        cam.style.display = 'none';
        document.body.classList.add('view-dashboard');
        document.body.classList.remove('view-camera');
        navDash.classList.add('active');
        navCam.classList.remove('active');
        if (topbarTitle) topbarTitle.textContent = 'Dashboard';
        setBottomNavActive('dashboard');
        stopCamera();
    }
}

function stopCamera() {
    if (stream) {
        stream.getTracks().forEach(track => track.stop());
        stream = null;
    }
    document.getElementById('video').srcObject = null;
    document.getElementById('btnScanMasuk').disabled = true;
    document.getElementById('btnScanPulang').disabled = true;
}

function addRecent(student, jenis) {
    const tbody = document.getElementById('recentListTable');
    if (!tbody) return;
    
    // Remove empty state row if exists
    const empty = tbody.querySelector('td[colspan]');
    if (empty) tbody.innerHTML = '';
    
    const time = new Date().toLocaleTimeString('id-ID', { hour12:false });
    const isMasuk = jenis === 'datang';
    const badgeClass = isMasuk ? 'bt-success' : 'bt-danger';
    const badgeText = isMasuk ? 'Hadir' : 'Pulang';
    
    const tr = document.createElement('tr');
    const d = new Date();
    const dateStr = d.getDate() + ' ' + d.toLocaleString('id-ID', { month: 'short' }) + ' ' + d.getFullYear();
    
    tr.innerHTML = `
        <td>${dateStr}</td>
        <td>${isMasuk ? time : '-'}</td>
        <td>${!isMasuk ? time : '-'}</td>
        <td><span class="badge-table ${badgeClass}">${badgeText}</span></td>
    `;
    tbody.prepend(tr);

    // Also update Status Hari Ini badge dynamically
    const statCards = document.querySelectorAll('.stat-card');
    if (statCards.length > 0) {
        const statusDiv = statCards[0].querySelector('.badge-warning, .badge-success');
        if (statusDiv) {
            statusDiv.className = 'badge-success';
            statusDiv.textContent = 'HADIR';
        }
        if (isMasuk && statCards[1]) {
            statCards[1].querySelector('.val').innerHTML = `<div class="stat-icon" style="background:var(--primary-light);color:var(--primary);"><i class="fas fa-clock"></i></div> ${time}`;
        } else if (!isMasuk && statCards[2]) {
            statCards[2].querySelector('.val').innerHTML = `<div class="stat-icon" style="background:var(--success-light);color:var(--success);"><i class="fas fa-clock"></i></div> ${time}`;
        }
    }

    // Update mobile stat cards
    const mobileCards = document.querySelectorAll('.mobile-stat-card');
    if (mobileCards.length >= 3) {
        const statusVal = mobileCards[0].querySelector('.mobile-stat-val');
        if (statusVal) {
            statusVal.className = 'mobile-stat-val success';
            statusVal.textContent = 'Hadir';
        }
        if (isMasuk) {
            const masukVal = mobileCards[1].querySelector('.mobile-stat-val');
            if (masukVal) masukVal.textContent = time.slice(0, 5);
        } else {
            const pulangVal = mobileCards[2].querySelector('.mobile-stat-val');
            if (pulangVal) pulangVal.textContent = time.slice(0, 5);
        }
    }
}

// ── DOMContentLoaded ──
document.addEventListener('DOMContentLoaded', () => {
    document.body.classList.add('view-dashboard');

    // Update Clock & Sisa Waktu Masuk every second
    const MASUK_CUTOFF = { h: 9, m: 0 }; // cutoff jam 09:00
    setInterval(() => {
        const d = new Date();
        const timeStr = d.toLocaleTimeString('id-ID', { hour12: false });
        const dateStr = d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        const clockEl = document.getElementById('liveClock');
        const dateEl = document.getElementById('dateLabel');
        if(clockEl) clockEl.textContent = timeStr;
        if(dateEl) dateEl.textContent = dateStr;

        // Sisa waktu masuk (desktop + mobile)
        const sisaEls = [
            document.getElementById('sisaWaktu'),
            document.getElementById('sisaWaktuMobile'),
        ].filter(Boolean);
        if (sisaEls.length) {
            const cutoff = new Date(d);
            cutoff.setHours(MASUK_CUTOFF.h, MASUK_CUTOFF.m, 0, 0);
            const diff = cutoff - d;
            let text, color;
            if (diff <= 0) {
                text = 'Waktu habis';
                color = 'var(--danger)';
            } else {
                const hh = String(Math.floor(diff / 3600000)).padStart(2,'0');
                const mm = String(Math.floor((diff % 3600000) / 60000)).padStart(2,'0');
                const ss = String(Math.floor((diff % 60000) / 1000)).padStart(2,'0');
                text = `${hh}:${mm}:${ss}`;
                color = diff < 1800000 ? 'var(--danger)' : 'var(--text-main)';
            }
            sisaEls.forEach(el => {
                el.textContent = text;
                el.style.color = color;
            });
        }
    }, 1000);

    // Init Dashboard Map
    initDashboardMap();
});

let dashMap = null;
function initDashboardMap() {
    if(!document.getElementById('mapDashboard')) return;
    dashMap = L.map('mapDashboard', { zoomControl: false }).setView([KANTOR_LAT, KANTOR_LNG], 16);
    L.tileLayer('https://{s}.basemaps.cartocdn.com/rastertiles/voyager/{z}/{x}/{y}{r}.png').addTo(dashMap);
    L.circle([KANTOR_LAT, KANTOR_LNG], { color: '#3b82f6', fillColor: '#3b82f6', fillOpacity: 0.2, radius: MAX_RADIUS }).addTo(dashMap);
    L.marker([KANTOR_LAT, KANTOR_LNG]).addTo(dashMap);
}

// Expose untuk onclick di Blade
window.switchView = switchView;
window.toggleSidebar = toggleSidebar;
window.openModal = openModal;
window.closeModal = closeModal;
window.startScan = startScan;
window.showToast = showToast;

})();
