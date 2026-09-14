import re

with open('/Users/giovedrahmatprabowo/face_recognition2/face_recognition2/resources/views/presensi/index.blade.php', 'r') as f:
    content = f.read()

# 1. Add real-time clock logic
clock_logic = """
// Update Clock
setInterval(() => {
    const d = new Date();
    const time = d.toLocaleTimeString('id-ID', { hour12: false }).replace(/\./g, ':');
    const dateStr = d.toLocaleDateString('id-ID', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
    const clockEl = document.getElementById('liveClock');
    const dateEl = document.getElementById('dateLabel');
    if(clockEl) clockEl.textContent = time;
    if(dateEl) dateEl.textContent = dateStr;
}, 1000);
"""
# Insert clock logic right after DOMContentLoaded
content = content.replace("document.addEventListener('DOMContentLoaded', () => {", "document.addEventListener('DOMContentLoaded', () => {\n" + clock_logic)


# 2. Fix setLocUI
# Looking at current script_modifications, we had:
# function setLocUI(type, title, sub) {
#     const statDiv = document.getElementById('camLocStatus');
# We need to make sure it exists and updates properly. Wait, FaceRecognitionEngine might be passing type='loading', 'ok', 'err'.
# Also we need to make sure `getLocation()` is actually called!
# Let's check where `getLocation` is called. It's usually called in FaceRecognitionEngine initialization, or we can call it on switchView('camera').
# Let's add getLocation() to switchView('camera') just to be safe.
content = content.replace("startCamera(); // Turn on camera when entering view", "startCamera(); \n        getLocation(); // Turn on camera when entering view")


# 3. Add Sakit and Cuti to Sidebar
sidebar_menu = """
            <button class="nav-item" onclick="openModal('izin')">
                <i class="fas fa-file-alt"></i> Pengajuan Izin
            </button>
            <button class="nav-item" onclick="openModal('sakit')">
                <i class="fas fa-briefcase-medical"></i> Pengajuan Sakit
            </button>
            <button class="nav-item" onclick="openModal('cuti')">
                <i class="fas fa-calendar-days"></i> Pengajuan Cuti
            </button>
"""
content = re.sub(r'<button class="nav-item" onclick="openModal\(\'izin\'\)">.*?Pengajuan Izin\s*</button>', sidebar_menu, content, flags=re.DOTALL)


# Add the modals for Sakit and Cuti if they are missing
sakit_cuti_modals = """
    <!-- Modal Sakit -->
    <div class="modal-overlay" id="modalSakit" style="z-index: 9999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pengajuan Sakit</h3>
                <button class="modal-close" onclick="closeModal('sakit')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('presensi.sakit') }}" method="POST" id="formSakit" enctype="multipart/form-data">
                    @csrf
                    <input type="hidden" name="jenis" value="sakit">
                    <div class="form-group">
                        <label class="form-label">Tanggal Sakit</label>
                        <input type="date" name="tanggal_mulai" class="form-control" value="{{ date('Y-m-d') }}" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Upload Surat Dokter</label>
                        <input type="file" name="bukti" class="form-control" accept=".jpg,.jpeg,.png,.pdf" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Keterangan / Penyakit</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Sakit demam, flu, dll..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-batal" style="width:auto; margin:0;" onclick="closeModal('sakit')">Batal</button>
                <button class="btn-presensi-besar" style="width:auto; padding:0.75rem 1.5rem; background:var(--danger);" onclick="document.getElementById('formSakit').submit()">Kirim Surat Sakit</button>
            </div>
        </div>
    </div>

    <!-- Modal Cuti -->
    <div class="modal-overlay" id="modalCuti" style="z-index: 9999;">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Pengajuan Cuti</h3>
                <button class="modal-close" onclick="closeModal('cuti')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('presensi.cuti') }}" method="POST" id="formCuti">
                    @csrf
                    <input type="hidden" name="jenis" value="cuti">
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:15px;">
                        <div class="form-group">
                            <label class="form-label">Mulai</label>
                            <input type="date" name="tanggal_mulai" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Selesai</label>
                            <input type="date" name="tanggal_selesai" class="form-control" required>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Alasan Cuti</label>
                        <textarea name="keterangan" class="form-control" rows="2" placeholder="Cuti tahunan, urusan keluarga..." required></textarea>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button class="btn-batal" style="width:auto; margin:0;" onclick="closeModal('cuti')">Batal</button>
                <button class="btn-presensi-besar" style="width:auto; padding:0.75rem 1.5rem; background:var(--primary);" onclick="document.getElementById('formCuti').submit()">Ajukan Cuti</button>
            </div>
        </div>
    </div>
"""

# Insert modals before toast-wrap
content = content.replace('<div class="toast-wrap" id="toastWrap"></div>', sakit_cuti_modals + '\n    <div class="toast-wrap" id="toastWrap"></div>')

# 4. Fix Z-index of Map and Modal
content = content.replace('class="modal-overlay" id="modalIzin"', 'class="modal-overlay" id="modalIzin" style="z-index: 9999;"')

# Ensure openModal and closeModal logic exists
modal_logic = """
function openModal(id) {
    const el = document.getElementById('modal' + id.charAt(0).toUpperCase() + id.slice(1));
    if(el) el.classList.add('show');
}
function closeModal(id) {
    const el = document.getElementById('modal' + id.charAt(0).toUpperCase() + id.slice(1));
    if(el) el.classList.remove('show');
}
"""
content = content.replace('// Intercept getLocation', modal_logic + '\n// Intercept getLocation')


with open('/Users/giovedrahmatprabowo/face_recognition2/face_recognition2/resources/views/presensi/index.blade.php', 'w') as f:
    f.write(content)

