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

## Troubleshooting umum

### Error `SQLSTATE[HY000] [2002] Connection refused` dengan `Host: 127.0.0.1`

Jika log menampilkan host `127.0.0.1` dan database `laravel`, artinya app masih memakai default Laravel (belum membaca koneksi DB Railway).

Langkah perbaikan:

1. Pastikan project punya service database (MySQL) aktif.
2. Di service app -> `Variables`, isi salah satu cara berikut:
   - Cara paling aman: `DB_URL=${{MySQL.MYSQL_URL}}`
   - Atau isi manual reference:
     - `DB_CONNECTION=mysql`
     - `DB_HOST=${{MySQL.MYSQLHOST}}`
     - `DB_PORT=${{MySQL.MYSQLPORT}}`
     - `DB_DATABASE=${{MySQL.MYSQLDATABASE}}`
     - `DB_USERNAME=${{MySQL.MYSQLUSER}}`
     - `DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}`
3. Jangan isi placeholder seperti `127.0.0.1`, `3306`, atau `laravel`.
4. Klik `Deploy`/`Redeploy` setelah update variables.

Catatan:
- Railway bisa menampilkan daftar `Suggested Variables`; pastikan nilainya benar-benar reference Railway, bukan teks contoh.
- Jika sebelumnya deploy gagal saat migrasi, lakukan redeploy ulang setelah variabel DB benar.
- Nama service di expression Railway bersifat spesifik. Jika service kamu bernama `mysql` (huruf kecil), gunakan `${{mysql.MYSQL_URL}}`, bukan `${{MySQL.MYSQL_URL}}`.
- Script `railway/predeploy.sh` di repo ini sekarang otomatis `optimize:clear` dan menampilkan ringkasan `DB_CONNECTION/DB_HOST/DB_PORT/DB_DATABASE` supaya lebih mudah cek apakah app masih jatuh ke default lokal.
- Nilai `<empty string>` pada `DB_HOST`, `DB_DATABASE`, `DB_USERNAME`, atau `DB_PASSWORD` tetap dianggap terisi dan bisa membuat fallback gagal. Lebih aman hapus variable kosong tersebut daripada menyisakan value kosong.
- `config/database.php` di repo ini juga sudah punya fallback ke env Railway `MYSQL_URL`/`MYSQLHOST`/`MYSQLPORT`/`MYSQLDATABASE`/`MYSQLUSER`/`MYSQLPASSWORD` jika `DB_*` kosong.

## Catatan penting untuk hosting sementara

- Filesystem Railway bersifat ephemeral. Upload file lokal (`storage`) bisa hilang saat redeploy/restart.
- Untuk kebutuhan permanen, pindahkan file upload ke object storage (misalnya S3/R2).
