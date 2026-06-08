# 🏥 E-Pharma Logistics

Sistem manajemen distribusi obat berbasis **microservice architecture** — dibangun dengan Laravel, MySQL, Docker, dan Midtrans.

---

## 📐 Arsitektur Microservice

Proyek ini terdiri dari **3 service independen** yang berkomunikasi satu sama lain:

```
┌─────────────────────────────────────────────────────────────┐
│                     NGINX Reverse Proxy                      │
│              (epharma-pa2.duckdns.org / localhost)           │
└──────────┬──────────────────┬──────────────────┬────────────┘
           │                  │                  │
           ▼                  ▼                  ▼
  ┌─────────────────┐ ┌──────────────┐ ┌─────────────────┐
  │ distribusi_obat │ │ auth_service │ │ report_service  │
  │   (Port 8000)   │ │  (Port 8001) │ │   (Port 8002)   │
  │                 │ │              │ │                 │
  │ • Manajemen     │ │ • Login/     │ │ • Analytics     │
  │   pesanan       │ │   Register   │ │   dashboard     │
  │ • Produk & stok │ │ • JWT token  │ │ • Laporan PDF/  │
  │ • Pembayaran    │ │ • OTP email  │ │   Excel         │
  │   Midtrans      │ │              │ │ • db_report     │
  └────────┬────────┘ └──────────────┘ └─────────────────┘
           │  sync otomatis (HTTP internal)
           └──────────────────────────────────►  db_report
```

### Cara Kerja Sync Data

`distribusi_obat` adalah satu-satunya service yang menulis data transaksi. Setiap kali ada perubahan (user baru, produk diupdate, pesanan dibuat/diubah), `distribusi_obat` secara otomatis mengirim data ke `report_service` via HTTP internal menggunakan `SyncReportService`.

```
distribusi_obat (db: e_pharma)
        │
        │  POST /api/internal/sync/user
        │  POST /api/internal/sync/product
        │  POST /api/internal/sync/order
        ▼
report_service (db: db_report)  ←── db snapshot, tidak pernah balik ke distribusi_obat
```

**Sifat sync: fire-and-forget** — jika `report_service` mati, transaksi di `distribusi_obat` tetap jalan normal. Hanya dashboard analytics yang tidak akan terupdate.

### Apa yang Terjadi Jika Satu Service Mati?

| Service Mati | Dampak |
|---|---|
| `auth_service` | Login/register tidak bisa. Fitur yang butuh autentikasi tidak bisa diakses. |
| `report_service` | Dashboard analytics tidak tampil data. Transaksi tetap bisa berjalan normal. |
| `distribusi_obat` | Seluruh aplikasi tidak bisa diakses. |

---

## 📋 Prasyarat

### Untuk Development Lokal

| Software | Versi Minimum | Link |
|---|---|---|
| PHP | 8.2 | https://www.php.net/downloads |
| Composer | 2.x | https://getcomposer.org |
| MySQL | 8.0 | https://dev.mysql.com/downloads |
| Node.js | 18.x | https://nodejs.org |
| ngrok | 3.x | https://ngrok.com/download |
| Git | terbaru | https://git-scm.com |

### Untuk Deployment (Production)

| Software | Keterangan |
|---|---|
| Docker | Container runtime |
| Docker Compose | Orkestrasi multi-container |
| Domain + DuckDNS | Untuk SSL dan akses publik |

---

## 🗂️ Struktur Proyek

```
PA-II-Kelompok-10/
├── distribusi_obat/          → Service utama (port 8000)
│   ├── app/Services/
│   │   └── SyncReportService.php   ← engine sync ke report_service
│   └── ...
├── auth_service/             → Service autentikasi (port 8001)
├── report_service/           → Service laporan (port 8002)
└── docker/
    ├── distribusi_obat.Dockerfile
    ├── auth_service.Dockerfile
    ├── report_service.Dockerfile
    ├── nginx-proxy.conf
    └── mysql-init.sql        ← buat db_auth & db_report otomatis
```

---

## 🚀 Menjalankan di Lokal

### 1. Clone Repository

```bash
git clone https://github.com/PA-II-Kelompok-10/PA-II-Kelompok-10.git
cd "PA-II-Kelompok-10"
```

### 2. Setup Setiap Service

Lakukan langkah ini untuk **masing-masing** folder (`distribusi_obat`, `auth_service`, `report_service`):

```bash
cd distribusi_obat

composer install
cp .env.example .env
php artisan key:generate
```

Ulangi untuk `auth_service` dan `report_service`.

### 3. Konfigurasi `.env`

#### `distribusi_obat/.env`

```env
APP_NAME=Laravel
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000   # diubah saat pakai ngrok

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e-pharma
DB_USERNAME=root
DB_PASSWORD=

MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

REPORT_SERVICE_URL=http://127.0.0.1:8002
INTERNAL_SECRET=rahasia-report-service-2024
```

#### `auth_service/.env`

```env
APP_NAME=AuthService
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_auth
DB_USERNAME=root
DB_PASSWORD=

JWT_SECRET=   # generate dengan: php artisan jwt:secret
```

#### `report_service/.env`

```env
APP_NAME=ReportService
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8002

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_report
DB_USERNAME=root
DB_PASSWORD=

INTERNAL_SECRET=rahasia-report-service-2024
```

> ⚠️ `INTERNAL_SECRET` harus sama di `distribusi_obat` dan `report_service`. Nilai ini dipakai untuk autentikasi request sync antar service.

### 4. Migrasi Database

```bash
# distribusi_obat & auth_service — migrate + seed
cd distribusi_obat && php artisan migrate --seed
cd auth_service && php artisan migrate --seed

# report_service — migrate saja, TANPA seed
# (data diisi otomatis via sync dari distribusi_obat)
cd report_service && php artisan migrate
```

### 5. Daftarkan URL ke Config Services

Di `distribusi_obat/config/services.php`, pastikan ada:

```php
'report_service' => [
    'url'    => env('REPORT_SERVICE_URL', 'http://127.0.0.1:8002'),
    'secret' => env('INTERNAL_SECRET', ''),
],
```

### 6. Jalankan Server (4 Terminal)

**Terminal 1 — distribusi_obat**
```bash
cd distribusi_obat
php artisan serve --host=0.0.0.0 --port=8000
```

**Terminal 2 — auth_service**
```bash
cd auth_service
php artisan serve --host=0.0.0.0 --port=8001
```

**Terminal 3 — report_service**
```bash
cd report_service
php artisan serve --host=0.0.0.0 --port=8002
```

**Terminal 4 — ngrok** (untuk Midtrans webhook)
```bash
ngrok http 8000
```

> ✅ Port 8003 sudah tidak diperlukan lagi. Sebelumnya dibutuhkan karena `report_service` memanggil balik ke `distribusi_obat` (menyebabkan deadlock). Setelah refactor arsitektur, `report_service` memiliki database snapshot sendiri (`db_report`) dan tidak pernah memanggil balik ke service lain.

### 7. Initial Sync Data (Pertama Kali / Setelah `migrate:fresh`)

Setelah migrasi selesai, isi `db_report` dengan data yang sudah ada:

```bash
cd distribusi_obat
php artisan tinker
```

```php
$sync = app(\App\Services\SyncReportService::class);
\App\Models\User::all()->each(fn($u) => $sync->syncUser($u));
\App\Models\Product::all()->each(fn($p) => $sync->syncProduct($p));
\App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn($o) => $sync->syncOrder($o));
```

Setelah ini, `db_report` akan terupdate otomatis setiap ada transaksi baru.

---

## 💳 Testing Pembayaran Midtrans (Lokal via ngrok)

### Setup ngrok

```bash
# Login ngrok (hanya sekali)
ngrok authtoken YOUR_TOKEN_HERE   # dapatkan di https://dashboard.ngrok.com/get-started/your-authtoken

# Jalankan
ngrok http 8000
```

Salin URL yang muncul, contoh: `https://xxxx-xxxx.ngrok-free.app`

### Update Konfigurasi

```env
# distribusi_obat/.env
APP_URL=https://xxxx-xxxx.ngrok-free.app
```

```bash
cd distribusi_obat
php artisan config:clear
php artisan cache:clear
```

### Daftarkan Webhook ke Midtrans

1. Login ke https://dashboard.sandbox.midtrans.com
2. Buka **Settings → Configuration**
3. Isi **Payment Notification URL**:
   ```
   https://xxxx-xxxx.ngrok-free.app/api/payment/webhook
   ```
4. Klik **Save**

### Data Kartu Test

| Field | Nilai |
|---|---|
| Nomor Kartu | `4811 1111 1111 1114` |
| Expiry | `01/39` |
| CVV | `123` |
| OTP/3DS | `112233` |

### Alur Pembayaran

```
Customer checkout
      ↓
Snap popup Midtrans muncul
      ↓
Customer bayar (pakai kartu test)
      ↓
Midtrans kirim webhook ke ngrok URL
      ↓
Status order berubah: Awaiting Payment → Pending (Lunas)
      ↓
Admin approve pesanan
```

> ⚠️ URL ngrok berubah setiap kali dimatikan dan dinyalakan ulang (kecuali akun berbayar). Setiap sesi baru, ulangi langkah update `APP_URL` dan webhook Midtrans.

> 💡 QRIS tidak bisa disimulasikan di sandbox Midtrans. Gunakan kartu kredit test.

---

## 🌐 Deployment ke Production (EC2 + Docker)

### Prasyarat di Server

- Ubuntu 22.04 / 24.04
- Docker & Docker Compose terinstall
- Domain terdaftar di DuckDNS (atau domain lain)
- File `.env` di server (tidak di-commit ke repo)

### Struktur File `.env` di Server

Buat file `.env` di root project (sejajar `docker-compose.yml`):

```env
MAIN_APP_URL=https://epharma-pa2.duckdns.org
AUTH_SERVICE_URL=http://epharma_auth
REPORT_SERVICE_URL=http://epharma_report

MAIN_APP_KEY=base64:xxxx
AUTH_APP_KEY=base64:xxxx
REPORT_APP_KEY=base64:xxxx

DB_PASSWORD=password_aman_kamu
INTERNAL_SECRET=rahasia-internal-kamu

JWT_SECRET=jwt_secret_kamu

MIDTRANS_SERVER_KEY=Mid-server-xxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=false

MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password_gmail
```

### Deploy Pertama Kali

```bash
# 1. Clone repo
git clone https://github.com/PA-II-Kelompok-10/PA-II-Kelompok-10.git
cd "PA-II-Kelompok-10"

# 2. Buat .env (isi sesuai di atas)
nano .env

# 3. Build & jalankan semua container
docker compose up -d --build

# 4. Migrasi database
docker compose exec distribusi_obat php artisan migrate --seed
docker compose exec auth_service php artisan migrate --seed
docker compose exec report_service php artisan migrate

# 5. Initial sync data ke report_service
docker compose exec distribusi_obat php artisan tinker --execute="
\$sync = app(\App\Services\SyncReportService::class);
\App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
\App\Models\Product::all()->each(fn(\$p) => \$sync->syncProduct(\$p));
\App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn(\$o) => \$sync->syncOrder(\$o));
echo 'done';
"
```

### Update / Redeploy

```bash
git pull origin main
docker compose up -d --build
```

Migrasi hanya dijalankan ulang jika ada file migrasi baru:
```bash
docker compose exec distribusi_obat php artisan migrate
docker compose exec auth_service php artisan migrate
docker compose exec report_service php artisan migrate
```

> ⚠️ Jangan gunakan `migrate:fresh` di production — itu akan menghapus semua data. Gunakan hanya di lingkungan development/staging.

### Reset Database (Hanya Development/Staging)

```bash
docker compose exec distribusi_obat php artisan migrate:fresh --seed
docker compose exec auth_service php artisan migrate:fresh --seed
docker compose exec report_service php artisan migrate:fresh

# Lalu sync ulang
docker compose exec distribusi_obat php artisan tinker --execute="
\$sync = app(\App\Services\SyncReportService::class);
\App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
\App\Models\Product::all()->each(fn(\$p) => \$sync->syncProduct(\$p));
\App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn(\$o) => \$sync->syncOrder(\$o));
echo 'done';
"
```

### Verifikasi Deployment

```bash
# Cek semua container running
docker compose ps

# Cek log jika ada error
docker compose logs --tail=50 distribusi_obat
docker compose logs --tail=50 auth_service
docker compose logs --tail=50 report_service

# Verifikasi sync data
docker compose exec report_service php artisan tinker --execute="
echo 'Users: ' . \App\Models\UserSnapshot::count() . PHP_EOL;
echo 'Products: ' . \App\Models\ProductSnapshot::count() . PHP_EOL;
echo 'Orders: ' . \App\Models\OrderSnapshot::count() . PHP_EOL;
"
```

---

## 🔁 Checklist Development Lokal

Setiap kali mulai development:

- [ ] Jalankan MySQL / XAMPP
- [ ] Terminal 1 → `distribusi_obat` port 8000
- [ ] Terminal 2 → `auth_service` port 8001
- [ ] Terminal 3 → `report_service` port 8002
- [ ] Terminal 4 → `ngrok http 8000`
- [ ] Update `APP_URL` di `.env` dengan URL ngrok terbaru
- [ ] Update webhook URL di dashboard Midtrans sandbox
- [ ] `php artisan config:clear && php artisan cache:clear` di distribusi_obat

---

## ❓ Troubleshooting

### CSS tidak muncul saat akses via ngrok
Pastikan `APP_URL` sudah diupdate dengan URL ngrok terbaru, lalu jalankan `php artisan config:clear`.

### Webhook Midtrans tidak masuk / status order tidak berubah
- Pastikan ngrok berjalan
- Pastikan URL webhook di Midtrans sudah diupdate
- Monitor request di http://127.0.0.1:4040

### Dashboard analytics error 503
- Pastikan `report_service` berjalan
- Pastikan `REPORT_SERVICE_URL` di `distribusi_obat` mengarah ke URL yang benar
- Jalankan initial sync jika `db_report` kosong

### `db_report` kosong / analytics tidak ada data
Jalankan ulang initial sync via tinker di `distribusi_obat` (lihat langkah 7).

### `env()` return null setelah `config:cache`
Ini terjadi karena variabel `env()` yang tidak didaftarkan di `config/` akan menjadi `null` setelah config di-cache. Pastikan semua variabel diakses via `config()`, bukan `env()` langsung di dalam kode aplikasi. Contoh untuk `REPORT_SERVICE_URL`, akses via `config('services.report_service.url')`.

### Error 500 di production tanpa detail
Aktifkan debug sementara di `docker-compose.yml`:
```yaml
APP_DEBUG: "true"
LOG_LEVEL: debug
```
Lalu:
```bash
docker compose up -d distribusi_obat
docker compose exec distribusi_obat tail -30 storage/logs/laravel.log
```
Kembalikan ke `false` setelah error ditemukan.

### `Class Midtrans\Config not found`
```bash
cd distribusi_obat
composer require midtrans/midtrans-php
php artisan config:clear
```

### `No query results for model ProductOrderStatus`
Database belum di-seed. Jalankan `php artisan db:seed`.

---

## 👥 Tim Pengembang

Kelompok 10 — Proyek Akhir II

© 2026 Yayasan Satriabudi Dharma Setia | E-Pharma Logistics