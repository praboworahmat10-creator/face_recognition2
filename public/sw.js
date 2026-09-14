/**
 * KOPPOSINDO FaceID — Service Worker
 * Cache shell + aset statis agar load lebih cepat di mobile.
 * API & presensi tetap network-first (data selalu fresh).
 */
const CACHE_VERSION = 'kopposindo-faceid-v4';
const STATIC_CACHE = `${CACHE_VERSION}-static`;
const RUNTIME_CACHE = `${CACHE_VERSION}-runtime`;

const PRECACHE_URLS = [
  '/manifest.webmanifest',
  '/js/pwa.js',
  '/js/presensi.js',
  '/js/FaceRecognitionEngine.js',
  '/js/face-api.js',
  '/css/presensi.css',
  '/css/custom.css',
  '/icons/icon-192.png',
  '/icons/icon-512.png',
  '/models/tiny_face_detector_model-weights_manifest.json',
  '/models/face_landmark_68_tiny_model-weights_manifest.json',
  '/models/face_recognition_model-weights_manifest.json',
];

self.addEventListener('install', (event) => {
  event.waitUntil(
    caches.open(STATIC_CACHE)
      .then((cache) => cache.addAll(PRECACHE_URLS))
      .then(() => self.skipWaiting())
      .catch(() => self.skipWaiting())
  );
});

self.addEventListener('activate', (event) => {
  event.waitUntil(
    caches.keys()
      .then((keys) => Promise.all(
        keys
          .filter((key) => key.startsWith('kopposindo-faceid-') && key !== STATIC_CACHE && key !== RUNTIME_CACHE)
          .map((key) => caches.delete(key))
      ))
      .then(() => self.clients.claim())
  );
});

function isApiOrAuth(url) {
  return url.pathname.startsWith('/api/')
    || url.pathname.startsWith('/presensi/simpan')
    || url.pathname.startsWith('/presensi/log-euclidean')
    || url.pathname.startsWith('/login')
    || url.pathname.startsWith('/logout');
}

function isStaticAsset(url) {
  return url.pathname.startsWith('/js/')
    || url.pathname.startsWith('/css/')
    || url.pathname.startsWith('/models/')
    || url.pathname.startsWith('/icons/')
    || url.pathname.endsWith('.webmanifest');
}

self.addEventListener('fetch', (event) => {
  const { request } = event;
  if (request.method !== 'GET') return;

  const url = new URL(request.url);
  if (url.origin !== self.location.origin) return;

  // Navigasi halaman: network-first, fallback cache
  if (request.mode === 'navigate') {
    event.respondWith(
      fetch(request)
        .then((response) => {
          if (response.ok && response.type === 'basic') {
            const copy = response.clone();
            caches.open(RUNTIME_CACHE).then((cache) => cache.put(request, copy));
          }
          return response;
        })
        .catch(() => caches.match(request))
    );
    return;
  }

  // API & auth: selalu network
  if (isApiOrAuth(url)) return;

  // Aset statis: cache-first
  if (isStaticAsset(url)) {
    event.respondWith(
      caches.match(request).then((cached) => {
        if (cached) return cached;
        return fetch(request).then((response) => {
          if (response.ok) {
            const copy = response.clone();
            caches.open(STATIC_CACHE).then((cache) => cache.put(request, copy));
          }
          return response;
        });
      })
    );
  }
});
