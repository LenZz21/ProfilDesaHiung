# Deploy Sementara ke Railway (Laravel)

Panduan ini untuk deploy cepat aplikasi ini ke Railway tanpa setup worker/cron terpisah.

## 1) Siapkan repository

1. Commit semua perubahan ke Git.
2. Push ke GitHub.

## 2) Buat project di Railway

1. Buat `New Project`.
2. Pilih `Deploy from GitHub repo`.
3. Pilih repository ini.

Railway akan mendeteksi Laravel dan menjalankan aplikasi via php-fpm + caddy.

## 3) Tambah database MySQL

1. Di project canvas Railway, klik `+ New`.
2. Pilih `MySQL`.

Setelah jadi, Railway memberi variabel seperti `MYSQL_URL`.

## 4) Isi environment variables app

1. Masuk ke service app.
2. Buka tab `Variables`.
3. Buka `Raw Editor`, lalu copy isi dari `.env.railway.example`.
4. Ubah nilai berikut:

- `APP_KEY`: isi dari output `php artisan key:generate --show`
- `APP_URL`: isi domain Railway app (setelah Generate Domain)
- `DB_URL`: biarkan `${{MySQL.MYSQL_URL}}` agar otomatis terhubung ke service MySQL

Catatan:
- `QUEUE_CONNECTION=sync` dipakai agar tidak perlu worker service.
- Notifikasi WhatsApp dimatikan default untuk mencegah timeout bila gateway belum ikut di-host.

## 5) Pre-deploy otomatis dari repo

Repo ini sudah punya `railway.json`:

- `preDeployCommand`: `chmod +x ./railway/predeploy.sh && ./railway/predeploy.sh`
- `healthcheckPath`: `/up`

Jadi kamu tidak perlu mengisi manual `Pre-Deploy Command` di dashboard, kecuali ingin override.

## 6) Generate domain

1. Masuk ke `Settings` -> `Networking`.
2. Klik `Generate Domain`.
3. Update `APP_URL` sesuai domain tersebut.
4. Klik `Redeploy`.

## 7) Verifikasi

1. Buka `https://your-app.up.railway.app/up` (harus `ok`).
2. Buka beranda aplikasi.
3. Cek log di Railway (`Deployments` / `Logs`) untuk memastikan tidak ada error bootstrap.

## Catatan penting untuk hosting sementara

- Filesystem Railway bersifat ephemeral. Upload file lokal (`storage`) bisa hilang saat redeploy/restart.
- Untuk kebutuhan permanen, pindahkan file upload ke object storage (misalnya S3/R2).
