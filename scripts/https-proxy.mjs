/**
 * HTTPS reverse proxy untuk akses mobile (kamera butuh secure context).
 * Proxy ke php artisan serve di localhost:8000
 */
import https from 'node:https';
import http from 'node:http';
import fs from 'node:fs';
import path from 'node:path';
import { fileURLToPath } from 'node:url';

const __dirname = path.dirname(fileURLToPath(import.meta.url));
const certDir = path.join(__dirname, '..', 'storage', 'certs');
const keyPath = path.join(certDir, 'mobile-key.pem');
const certPath = path.join(certDir, 'mobile-cert.pem');
const TARGET_HOST = '127.0.0.1';
const TARGET_PORT = Number(process.env.APP_PORT || 8000);
const HTTPS_PORT = Number(process.env.HTTPS_PORT || 8443);

if (!fs.existsSync(keyPath) || !fs.existsSync(certPath)) {
  console.error('[HTTPS] Sertifikat belum ada. Jalankan: npm run serve:mobile');
  process.exit(1);
}

const serverOptions = {
  key: fs.readFileSync(keyPath),
  cert: fs.readFileSync(certPath),
};

https.createServer(serverOptions, (clientReq, clientRes) => {
  const proxyReq = http.request(
    {
      hostname: TARGET_HOST,
      port: TARGET_PORT,
      path: clientReq.url,
      method: clientReq.method,
      headers: {
        ...clientReq.headers,
        host: `${TARGET_HOST}:${TARGET_PORT}`,
      },
    },
    (proxyRes) => {
      clientRes.writeHead(proxyRes.statusCode || 502, proxyRes.headers);
      proxyRes.pipe(clientRes);
    }
  );

  proxyReq.on('error', () => {
    clientRes.writeHead(502, { 'Content-Type': 'text/plain; charset=utf-8' });
    clientRes.end('Backend tidak tersedia. Pastikan php artisan serve berjalan di port ' + TARGET_PORT);
  });

  clientReq.pipe(proxyReq);
}).listen(HTTPS_PORT, '0.0.0.0', () => {
  console.log(`[HTTPS] Proxy aktif di port ${HTTPS_PORT} → http://${TARGET_HOST}:${TARGET_PORT}`);
});
