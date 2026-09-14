/**
 * KOPPOSINDO FaceID — PWA bootstrap
 * Registrasi service worker + prompt install + deep link ke kamera
 */
(function () {
  'use strict';

  const DISMISS_KEY = 'pwa-install-dismissed-session';
  let deferredInstallPrompt = null;

  // Reset dismiss lama (7 hari) agar banner bisa muncul lagi
  try { localStorage.removeItem('pwa-install-dismissed'); } catch (e) {}

  function registerServiceWorker() {
    if (!('serviceWorker' in navigator)) return;

    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js', { scope: '/' })
        .then((reg) => {
          reg.addEventListener('updatefound', () => {
            const worker = reg.installing;
            if (!worker) return;
            worker.addEventListener('statechange', () => {
              if (worker.state === 'installed' && navigator.serviceWorker.controller) {
                showUpdateToast();
              }
            });
          });
        })
        .catch((err) => console.warn('[PWA] SW registration failed:', err));
    });
  }

  function showUpdateToast() {
    const wrap = document.getElementById('toastWrap');
    if (!wrap) return;

    const t = document.createElement('div');
    t.className = 'toast info';
    t.innerHTML = `
      <i class="fas fa-arrows-rotate" style="color:var(--primary);font-size:15px;"></i>
      <span>Versi baru tersedia.</span>
      <button type="button" id="pwaReloadBtn" style="margin-left:auto;background:var(--primary);color:#fff;border:none;border-radius:6px;padding:4px 10px;font-size:0.8rem;font-weight:600;cursor:pointer;">Muat Ulang</button>
    `;
    wrap.appendChild(t);
    t.querySelector('#pwaReloadBtn')?.addEventListener('click', () => window.location.reload());
  }

  function dismissBanner() {
    document.getElementById('pwaInstallBanner')?.remove();
    try { sessionStorage.setItem(DISMISS_KEY, '1'); } catch (e) {}
  }

  function bindDismissButton(banner) {
    banner.querySelector('#pwaInstallDismiss')?.addEventListener('click', dismissBanner);
  }

  function createInstallBanner() {
    if (document.getElementById('pwaInstallBanner')) return;

    const banner = document.createElement('div');
    banner.id = 'pwaInstallBanner';
    banner.innerHTML = `
      <div class="pwa-install-inner">
        <div class="pwa-install-icon"><i class="fas fa-mobile-screen-button"></i></div>
        <div class="pwa-install-text">
          <strong>Install FaceID</strong>
          <span>Akses presensi lebih cepat dari layar utama HP</span>
        </div>
        <button type="button" class="pwa-install-btn" id="pwaInstallBtn">Install</button>
        <button type="button" class="pwa-install-close" id="pwaInstallDismiss" aria-label="Tutup"><i class="fas fa-times"></i></button>
      </div>
    `;
    document.body.appendChild(banner);
    bindDismissButton(banner);

    document.getElementById('pwaInstallBtn')?.addEventListener('click', async () => {
      if (!deferredInstallPrompt) {
        if (typeof showToast === 'function') {
          showToast('Buka menu browser (⋮) → Install app / Add to Home screen', 'info', 6000);
        }
        return;
      }
      deferredInstallPrompt.prompt();
      const { outcome } = await deferredInstallPrompt.userChoice;
      deferredInstallPrompt = null;
      banner.remove();
      if (outcome === 'accepted' && typeof showToast === 'function') {
        showToast('FaceID berhasil di-install!', 'success');
      }
    });
  }

  function isIosSafari() {
    const ua = window.navigator.userAgent;
    const isIOS = /iPad|iPhone|iPod/.test(ua) || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
    return isIOS && !isStandalone();
  }

  function createIosInstallBanner() {
    if (document.getElementById('pwaInstallBanner')) return;

    const banner = document.createElement('div');
    banner.id = 'pwaInstallBanner';
    banner.innerHTML = `
      <div class="pwa-install-inner">
        <div class="pwa-install-icon"><i class="fas fa-mobile-screen-button"></i></div>
        <div class="pwa-install-text">
          <strong>Install FaceID di iPhone</strong>
          <span>Ketuk <i class="fas fa-arrow-up-from-bracket" style="font-size:0.7rem;"></i> Share → "Add to Home Screen"</span>
        </div>
        <button type="button" class="pwa-install-close" id="pwaInstallDismiss" aria-label="Tutup"><i class="fas fa-times"></i></button>
      </div>
    `;
    document.body.appendChild(banner);
    bindDismissButton(banner);
  }

  function createGenericInstallBanner() {
    if (document.getElementById('pwaInstallBanner')) return;

    const isMobile = /Android|iPhone|iPad|iPod/i.test(navigator.userAgent);
    const hint = isMobile
      ? 'Chrome: menu ⋮ → "Install app" atau "Add to Home screen"'
      : 'Chrome: ikon install di address bar, atau menu ⋮ → "Install FaceID"';

    const banner = document.createElement('div');
    banner.id = 'pwaInstallBanner';
    banner.innerHTML = `
      <div class="pwa-install-inner">
        <div class="pwa-install-icon"><i class="fas fa-download"></i></div>
        <div class="pwa-install-text">
          <strong>Install FaceID</strong>
          <span>${hint}</span>
        </div>
        <button type="button" class="pwa-install-close" id="pwaInstallDismiss" aria-label="Tutup"><i class="fas fa-times"></i></button>
      </div>
    `;
    document.body.appendChild(banner);
    bindDismissButton(banner);
  }

  function isStandalone() {
    return window.matchMedia('(display-mode: standalone)').matches
      || window.navigator.standalone === true;
  }

  function wasDismissedThisSession() {
    try { return sessionStorage.getItem(DISMISS_KEY) === '1'; } catch (e) { return false; }
  }

  function showInstallBanner(force = false) {
    if (isStandalone()) {
      if (typeof showToast === 'function') {
        showToast('FaceID sudah ter-install di perangkat ini.', 'info');
      }
      return;
    }

    if (!force && wasDismissedThisSession()) return;

    document.getElementById('pwaInstallBanner')?.remove();

    if (deferredInstallPrompt) {
      createInstallBanner();
    } else if (isIosSafari()) {
      createIosInstallBanner();
    } else {
      createGenericInstallBanner();
    }
  }

  function maybeShowInstallBanner(force = false) {
    if (isStandalone()) return;
    showInstallBanner(force);
  }

  // Dipanggil dari tombol sidebar: showPwaInstall()
  window.showPwaInstall = function () {
    try { sessionStorage.removeItem(DISMISS_KEY); } catch (e) {}
    showInstallBanner(true);
  };

  function updateInstallMenuVisibility() {
    const btn = document.getElementById('pwaInstallMenuBtn');
    if (btn) btn.style.display = isStandalone() ? 'none' : 'flex';
  }

  function setupInstallPrompt() {
    window.addEventListener('beforeinstallprompt', (e) => {
      e.preventDefault();
      deferredInstallPrompt = e;
      maybeShowInstallBanner(false);
    });

    window.addEventListener('appinstalled', () => {
      deferredInstallPrompt = null;
      document.getElementById('pwaInstallBanner')?.remove();
      updateInstallMenuVisibility();
      if (typeof showToast === 'function') showToast('FaceID siap digunakan!', 'success');
    });

    document.addEventListener('DOMContentLoaded', () => {
      updateInstallMenuVisibility();

      const params = new URLSearchParams(window.location.search);
      const forceInstall = params.get('install') === '1';

      if (forceInstall) {
        window.showPwaInstall();
      } else {
        setTimeout(() => maybeShowInstallBanner(false), 1500);
      }
    });
  }

  function handleDeepLink() {
    const params = new URLSearchParams(window.location.search);
    if (params.get('action') !== 'camera') return;

    const openCamera = () => {
      if (typeof switchView === 'function') switchView('camera');
    };

    if (document.readyState === 'loading') {
      document.addEventListener('DOMContentLoaded', openCamera);
    } else {
      setTimeout(openCamera, 300);
    }
  }

  function injectStyles() {
    if (document.getElementById('pwa-styles')) return;
    const style = document.createElement('style');
    style.id = 'pwa-styles';
    style.textContent = `
      #pwaInstallBanner {
        position: fixed; bottom: 0; left: 0; right: 0; z-index: 10000;
        padding: 12px 16px calc(12px + env(safe-area-inset-bottom));
        background: rgba(15,23,42,0.95); backdrop-filter: blur(8px);
        border-top: 1px solid rgba(255,255,255,0.1);
        animation: pwaSlideUp 0.3s ease;
      }
      @keyframes pwaSlideUp { from { transform: translateY(100%); } to { transform: translateY(0); } }
      .pwa-install-inner { display: flex; align-items: center; gap: 12px; max-width: 600px; margin: 0 auto; }
      .pwa-install-icon {
        width: 40px; height: 40px; border-radius: 10px; flex-shrink: 0;
        background: #3B82F6; color: white; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;
      }
      .pwa-install-text { flex: 1; color: white; line-height: 1.3; min-width: 0; }
      .pwa-install-text strong { display: block; font-size: 0.9rem; margin-bottom: 2px; }
      .pwa-install-text span { font-size: 0.75rem; color: #94A3B8; }
      .pwa-install-btn {
        background: #3B82F6; color: white; border: none; border-radius: 8px;
        padding: 8px 16px; font-weight: 700; font-size: 0.85rem; cursor: pointer; flex-shrink: 0;
      }
      .pwa-install-close {
        background: none; border: none; color: #64748B; font-size: 1rem; cursor: pointer; padding: 4px; flex-shrink: 0;
      }
      @media (min-width: 769px) {
        #pwaInstallBanner { left: 260px; }
      }
    `;
    document.head.appendChild(style);
  }

  registerServiceWorker();
  setupInstallPrompt();
  handleDeepLink();
  injectStyles();
})();
