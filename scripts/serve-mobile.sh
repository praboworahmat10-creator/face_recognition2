#!/usr/bin/env bash
set -euo pipefail

cd "$(dirname "$0")/.."

IP="$(ipconfig getifaddr en0 2>/dev/null || ipconfig getifaddr en1 2>/dev/null || echo "127.0.0.1")"
HTTP_PORT="${APP_PORT:-8000}"
HTTPS_PORT="${HTTPS_PORT:-8443}"
CERT_DIR="storage/certs"

mkdir -p "$CERT_DIR"

echo "→ Membuat/memperbarui sertifikat SSL untuk IP: $IP"
openssl req -x509 -newkey rsa:2048 \
  -keyout "$CERT_DIR/mobile-key.pem" \
  -out "$CERT_DIR/mobile-cert.pem" \
  -days 365 -nodes \
  -subj "/CN=KOPPOSINDO-Mobile" \
  -addext "subjectAltName=IP:${IP},IP:127.0.0.1,DNS:localhost" \
  2>/dev/null

echo ""
echo "============================================"
echo "  KOPPOSINDO — Server Mobile Dev"
echo "============================================"
echo "  WiFi HP harus SAMA dengan Mac/laptop"
echo ""
echo "  HTTPS (kamera presensi):"
echo "  https://${IP}:${HTTPS_PORT}/login"
echo ""
echo "  HTTP (dashboard saja):"
echo "  http://${IP}:${HTTP_PORT}/login"
echo ""
echo "  Di HP: terima peringatan sertifikat"
echo "  (Advanced → Proceed / Lanjutkan)"
echo "============================================"
echo ""

cleanup() {
  kill "$PHP_PID" "$NODE_PID" 2>/dev/null || true
}
trap cleanup EXIT INT TERM

php artisan serve --host=0.0.0.0 --port="$HTTP_PORT" &
PHP_PID=$!

sleep 1

HTTPS_PORT="$HTTPS_PORT" APP_PORT="$HTTP_PORT" node scripts/https-proxy.mjs &
NODE_PID=$!

wait
