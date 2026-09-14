import re

with open('/Users/giovedrahmatprabowo/face_recognition2/face_recognition2/resources/views/presensi/index.blade.php', 'r') as f:
    content = f.read()

# We need to replace the original `getLocation` function entirely, or fix it.
# Instead of doing complex string manipulation, let's just use regex to replace the old `getLocation` function completely.
# Find `function getLocation() { ... }`
# It's at line 1039 to 1093.

new_get_location = """
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
"""

content = re.sub(r'let locMap = null;.*?function getLocation\(\) \{.*?(?=\nfunction setLocUI)', new_get_location, content, flags=re.DOTALL)

# Also remove the intercepted getLocation since we combined it
content = re.sub(r'// Intercept getLocation.*?originalGetLoc\(\);\n\};\n', '', content, flags=re.DOTALL)


with open('/Users/giovedrahmatprabowo/face_recognition2/face_recognition2/resources/views/presensi/index.blade.php', 'w') as f:
    f.write(content)

