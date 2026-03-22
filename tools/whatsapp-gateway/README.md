# WhatsApp Bot Minimal

Bot ini versi minimal, gratis (unofficial), dengan fitur:

- QR login
- status koneksi
- kirim pesan via API
- auto-reply pesan masuk

## Jalankan

```bash
cd tools/whatsapp-gateway
copy .env.example .env
```

Install dependency (tanpa download Chromium):

```bash
set PUPPETEER_SKIP_DOWNLOAD=true
npm install
```

Start bot:

```bash
npm start
```

## Mode 24 Jam (Windows + PM2)

Dari root project `ProfilDesa`, jalankan:

```powershell
powershell -NoProfile -ExecutionPolicy Bypass -File .\scripts\install-whatsapp-gateway-autostart.ps1
```

Script ini akan:
- memastikan proses `whatsapp-gateway` berjalan di PM2,
- menyimpan proses PM2 (`pm2 save`),
- membuat autostart saat login Windows (Task Scheduler user).

Penting:
- Jika sudah pakai PM2, jangan jalankan `npm start` manual bersamaan, karena bisa bentrok port `3070`.
- Cek status proses: `pm2 ls`
- Cek log: `pm2 logs whatsapp-gateway --lines 100`

Catatan:
- Bot tetap butuh laptop/PC menyala dan user login.
- Jika laptop sleep/hibernate atau mati listrik, bot ikut berhenti sampai perangkat aktif lagi.

## Auto Reply

Atur di file `.env`:

```env
WA_AUTO_REPLY_ENABLED=true
WA_AUTO_REPLY_TEXT=Halo, pesan Anda sudah masuk ke Admin Kampung. Mohon tunggu, kami akan membalas secepatnya.
WA_AUTO_REPLY_COOLDOWN_SECONDS=0
WA_AUTO_REPLY_IGNORE_NUMBERS=
WA_HEALTHCHECK_INTERVAL_MS=30000
WA_UNHEALTHY_RECONNECT_AFTER_MS=120000
WA_SERVER_BIND_RETRY_MS=5000
```

Catatan:
- `WA_AUTO_REPLY_COOLDOWN_SECONDS` mencegah spam balasan ke nomor yang sama.
- Auto-reply tidak dikirim ke grup (`@g.us`) dan status broadcast.

## Endpoint

- `GET /api/v1/status`
- `GET /api/v1/qr`
- `GET /api/v1/qr-view`
- `POST /api/v1/send-message`

Contoh body `send-message`:

```json
{
  "to": "6281234567890",
  "message": "Halo dari bot minimal"
}
```
