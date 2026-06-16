# 🏥 SI-DOBAT — Sistem Informasi Distribusi Obat

Sistem manajemen distribusi obat berbasis **microservice architecture** — dibangun dengan Laravel, MySQL, Apache, dan Midtrans. Dikembangkan oleh Kelompok 10 sebagai Proyek Akhir II D3 Teknologi Informasi, Institut Teknologi Del.

---

## 📐 Arsitektur Microservice

Proyek ini terdiri dari **3 service independen** yang berkomunikasi satu sama lain:

```
┌─────────────────────────────────────────────────────────────┐
│                     Apache Web Server                        │
│              (kel10-pa2.d3ti-itdel.web.id)                  │
└──────────┬──────────────────┬──────────────────┬────────────┘
           │                  │                  │
           ▼                  ▼                  ▼
  ┌─────────────────┐ ┌──────────────┐ ┌─────────────────┐
  │ distribusi_obat │ │ auth_service │ │ report_service  │
  │  (Port 80/443)  │ │  (Port 8001) │ │   (Port 8002)   │
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

|    Service Mati   |                                    Dampak                                    |
|-------------------|------------------------------------------------------------------------------|
| `auth_service`    | Login/register tidak bisa. Fitur yang butuh autentikasi tidak bisa diakses.  |
| `report_service`  | Dashboard analytics tidak tampil data. Transaksi tetap bisa berjalan normal. |
| `distribusi_obat` | Seluruh aplikasi tidak bisa diakses.                                         |

---

## 🗂️ Struktur Proyek

```
PA-II-Kelompok-10/
├── distribusi_obat/               → Service utama (port 80/443)
│   ├── app/
│   │   ├── Services/
│   │   │   └── SyncReportService.php   ← engine sync ke report_service
│   │   ├── Http/Controllers/Api/
│   │   │   ├── AdminController.php
│   │   │   ├── ProductController.php
│   │   │   └── ProductOrderController.php
│   │   ├── Models/
│   │   └── Exports/
│   ├── database/
│   │   ├── migrations/
│   │   └── seeders/
│   │       └── RolePermissionSeeder.php
│   └── config/
│       └── services.php            ← konfigurasi URL report_service
├── auth_service/                  → Service autentikasi (port 8001)
│   ├── app/Http/Controllers/
│   └── database/
└── report_service/                → Service laporan (port 8002)
    ├── app/
    │   ├── Http/Controllers/
    │   │   ├── SyncController.php  ← penerima sync dari distribusi_obat
    │   │   └── ReportController.php
    │   ├── Models/
    │   │   ├── UserSnapshot.php
    │   │   ├── OrderSnapshot.php
    │   │   └── ProductSnapshot.php
    │   └── Exports/
    │       └── UsersExport.php
    └── database/migrations/
```

---

## 👥 Role & Permission

Sistem menggunakan **Spatie Laravel Permission** dengan 4 role:

| Role | Permission | Akses |
|---|---|---|
| `admin` | Semua permission | Kelola user, produk, pesanan, laporan |
| `operator` | `manage users`, `manage inventory`, `view reports` | Kelola stok & laporan |
| `courier` | `delivery task` | Lihat & update status pengiriman |
| `customer` | `create request` | Buat pesanan & pembayaran |

### Alur Registrasi Customer

```
Customer register → OTP via email → Verifikasi OTP
       ↓
Status: pending (status=0)
       ↓
Admin approve di backoffice
       ↓
Status: aktif (status=1) → Customer bisa login & pesan
```

---

## 🔄 Alur Bisnis Lengkap

```
Register → OTP → Admin Approve → Login
                                    ↓
                             Pilih Produk → Checkout
                                    ↓
                          Midtrans Snap Popup
                                    ↓
                             Bayar (sandbox)
                                    ↓
                    Webhook → status: Pending (Lunas)
                                    ↓
                           Admin Approve Pesanan
                                    ↓
                         Operator Assign Kurir
                                    ↓
                     Kurir Update Status Pengiriman
                                    ↓
                              Pesanan Selesai
```

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

### Untuk Deployment di Server (Production)

| Software | Keterangan |
|---|---|
| Ubuntu 22.04 / 24.04 | OS server |
| Apache2 + mod_rewrite | Web server multi-port |
| PHP 8.3 + php-fpm | Runtime PHP |
| MySQL 8.0 | Database |
| Certbot + Let's Encrypt | SSL untuk domain utama |

---

## 🚀 Menjalankan di Lokal

### 1. Clone Repository

```bash
git clone https://github.com/bryansiagian/PA-II-Kelompok-10.git
cd PA-II-Kelompok-10
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
APP_NAME=SI-DOBAT
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost:8000   # diubah saat pakai ngrok

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_pharma
DB_USERNAME=root
DB_PASSWORD=

AUTH_SERVICE_URL=http://127.0.0.1:8001
REPORT_SERVICE_URL=http://127.0.0.1:8002
INTERNAL_SECRET=rahasia-report-service-2024

MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_ENCRYPTION=tls
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

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_ENCRYPTION=tls
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

> ⚠️ `INTERNAL_SECRET` harus **sama persis** di `distribusi_obat` dan `report_service`. Nilai ini dipakai untuk autentikasi request sync antar service.

### 4. Konfigurasi `config/services.php` di distribusi_obat

Pastikan file `distribusi_obat/config/services.php` memiliki entry berikut:

```php
'report_service' => [
    'url'    => env('REPORT_SERVICE_URL', 'http://127.0.0.1:8002'),
    'secret' => env('INTERNAL_SECRET', ''),
],
```

### 5. Migrasi Database

```bash
# distribusi_obat — migrate + seed (termasuk role, permission, user & kendaraan contoh)
cd distribusi_obat && php artisan migrate --seed

# auth_service — migrate + seed
cd ../auth_service && php artisan migrate --seed

# report_service — migrate TANPA seed (data diisi otomatis via sync)
cd ../report_service && php artisan migrate
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

### 7. Initial Sync Data (Pertama Kali / Setelah `migrate:fresh`)

Setelah migrasi selesai, isi `db_report` dengan data awal dari `distribusi_obat`:

```bash
cd distribusi_obat
php artisan tinker --execute="
\$sync = app(App\Services\SyncReportService::class);
App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
App\Models\Product::with('category')->get()->each(fn(\$p) => \$sync->syncProduct(\$p));
App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn(\$o) => \$sync->syncOrder(\$o));
echo 'Sync selesai';
"
```

Setelah ini, `db_report` akan terupdate **otomatis** setiap ada transaksi baru.

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
php artisan config:clear && php artisan cache:clear
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

> ⚠️ URL ngrok berubah setiap kali dimatikan dan dinyalakan ulang (kecuali akun berbayar). Setiap sesi baru, ulangi langkah update `APP_URL` dan webhook Midtrans.

> 💡 QRIS tidak bisa disimulasikan di sandbox Midtrans. Gunakan kartu kredit test di atas.

---

## 🌐 Deployment — Server Dosen (Apache, Tanpa Docker)

> Ini adalah deployment aktif yang digunakan untuk sidang akademik.
> URL: **https://kel10-pa2.d3ti-itdel.web.id**

### Arsitektur di Server

| Service | Port | URL |
|---|---|---|
| `distribusi_obat` | 80 / 443 (HTTPS) | `https://kel10-pa2.d3ti-itdel.web.id` |
| `auth_service` | 8001 | `http://kel10-pa2.d3ti-itdel.web.id:8001` |
| `report_service` | 8002 | `http://kel10-pa2.d3ti-itdel.web.id:8002` |

### Prasyarat Server

Server sudah dikonfigurasi oleh dosen dengan:
- Apache2 dengan `mod_rewrite` aktif
- PHP 8.3 + PHP-FPM
- MySQL 8.0
- Let's Encrypt SSL untuk domain utama
- Multi-port listening di `/etc/apache2/ports.conf`

### Konfigurasi Apache (`/etc/apache2/ports.conf`)

```apache
Listen 80
Listen 8001   # auth_service Kelompok 10
Listen 8002   # report_service Kelompok 10
Listen 443
```

### Deploy Pertama Kali

```bash
# 1. Clone repo ke direktori server
cd /var/www/html
sudo git clone https://github.com/bryansiagian/PA-II-Kelompok-10.git
cd PA-II-Kelompok-10

# 2. Install dependencies untuk setiap service
cd distribusi_obat && composer install --no-dev --optimize-autoloader
cd ../auth_service && composer install --no-dev --optimize-autoloader
cd ../report_service && composer install --no-dev --optimize-autoloader

# 3. Buat .env untuk setiap service (dari .env.example, lalu isi nilainya)
cp distribusi_obat/.env.example distribusi_obat/.env
cp auth_service/.env.example auth_service/.env
cp report_service/.env.example report_service/.env

# 4. Generate app key
cd distribusi_obat && php artisan key:generate
cd ../auth_service && php artisan key:generate && php artisan jwt:secret
cd ../report_service && php artisan key:generate

# 5. Set permission storage & cache
sudo chown -R www-data:www-data /var/www/html/PA-II-Kelompok-10
sudo chmod -R 775 /var/www/html/PA-II-Kelompok-10/distribusi_obat/storage
sudo chmod -R 775 /var/www/html/PA-II-Kelompok-10/auth_service/storage
sudo chmod -R 775 /var/www/html/PA-II-Kelompok-10/report_service/storage

# 6. Migrasi database
cd /var/www/html/PA-II-Kelompok-10/distribusi_obat && php artisan migrate --seed
cd /var/www/html/PA-II-Kelompok-10/auth_service && php artisan migrate --seed
cd /var/www/html/PA-II-Kelompok-10/report_service && php artisan migrate

# 7. Initial sync data ke report_service
php artisan tinker --execute="
\$sync = app(App\Services\SyncReportService::class);
App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
App\Models\Product::with('category')->get()->each(fn(\$p) => \$sync->syncProduct(\$p));
App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn(\$o) => \$sync->syncOrder(\$o));
echo 'Sync selesai';
"
```

### Konfigurasi `.env` di Server

#### `distribusi_obat/.env` (server)

```env
APP_NAME=SI-DOBAT
APP_ENV=production
APP_DEBUG=false
APP_URL=https://kel10-pa2.d3ti-itdel.web.id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e_pharma
DB_USERNAME=your_db_user
DB_PASSWORD=your_db_password

AUTH_SERVICE_URL=http://kel10-pa2.d3ti-itdel.web.id:8001
REPORT_SERVICE_URL=http://kel10-pa2.d3ti-itdel.web.id:8002
INTERNAL_SECRET=your_internal_secret

MIDTRANS_SERVER_KEY=Mid-server-xxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=false

MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=email@gmail.com
MAIL_PASSWORD=app_password_gmail
MAIL_ENCRYPTION=tls
```

#### `report_service/.env` (server)

```env
APP_ENV=production
APP_URL=http://kel10-pa2.d3ti-itdel.web.id:8002
INTERNAL_SECRET=your_internal_secret   # harus sama dengan distribusi_obat
```

### Midtrans Webhook (Production Server)

Karena `distribusi_obat` sudah HTTPS, webhook Midtrans bisa langsung pakai domain dosen:

1. Login ke https://dashboard.sandbox.midtrans.com
2. **Settings → Configuration**
3. **Payment Notification URL**:
   ```
   https://kel10-pa2.d3ti-itdel.web.id/api/payment/webhook
   ```

### Update / Redeploy

```bash
cd /var/www/html/PA-II-Kelompok-10
sudo git pull origin main

# Jalankan migrasi hanya jika ada file migrasi baru
cd distribusi_obat && php artisan migrate --force
cd ../auth_service && php artisan migrate --force
cd ../report_service && php artisan migrate --force

# Clear cache setelah update
cd /var/www/html/PA-II-Kelompok-10/distribusi_obat
php artisan config:clear && php artisan cache:clear && php artisan route:clear

# Restart PHP-FPM agar perubahan kode terbaca (clear opcache)
sudo systemctl restart php8.3-fpm
```

> ⚠️ **Jangan gunakan `migrate:fresh` di server** — semua data akan terhapus. Gunakan hanya saat reset total untuk keperluan testing.

### Reset Total (Testing Only)

```bash
cd /var/www/html/PA-II-Kelompok-10/distribusi_obat && php artisan migrate:fresh --seed --force
cd /var/www/html/PA-II-Kelompok-10/auth_service && php artisan migrate:fresh --seed --force
cd /var/www/html/PA-II-Kelompok-10/report_service && php artisan migrate:fresh --force

# Sync ulang semua data ke report_service
cd /var/www/html/PA-II-Kelompok-10/distribusi_obat && php artisan tinker --execute="
\$sync = app(App\Services\SyncReportService::class);
App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
App\Models\Product::with('category')->get()->each(fn(\$p) => \$sync->syncProduct(\$p));
App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn(\$o) => \$sync->syncOrder(\$o));
echo 'Sync selesai';
"
```

---

## 🐳 Deployment — AWS EC2 + Docker (Alternatif)

> Deployment mandiri menggunakan Docker Compose dan DuckDNS.
> URL: **https://epharma-pa2.duckdns.org**

### Prasyarat

- AWS EC2 instance (Ubuntu 22.04) dengan Elastic IP
- Docker & Docker Compose terinstall
- Domain terdaftar di DuckDNS

### Struktur File Docker

```
PA-II-Kelompok-10/
├── docker/
│   ├── distribusi_obat.Dockerfile
│   ├── auth_service.Dockerfile
│   ├── report_service.Dockerfile
│   ├── nginx-proxy.conf
│   └── mysql-init.sql        ← buat db_auth & db_report otomatis
└── docker-compose.yml
```

### File `.env` di Root Project

Buat file `.env` sejajar `docker-compose.yml`:

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

### Deploy

```bash
# 1. Clone repo
git clone https://github.com/bryansiagian/PA-II-Kelompok-10.git
cd PA-II-Kelompok-10

# 2. Buat .env
nano .env

# 3. Build & jalankan semua container
docker compose up -d --build

# 4. Migrasi database
docker compose exec distribusi_obat php artisan migrate --seed
docker compose exec auth_service php artisan migrate --seed
docker compose exec report_service php artisan migrate

# 5. Initial sync data
docker compose exec distribusi_obat php artisan tinker --execute="
\$sync = app(App\Services\SyncReportService::class);
App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
App\Models\Product::with('category')->get()->each(fn(\$p) => \$sync->syncProduct(\$p));
App\Models\ProductOrder::with(['status','items.product'])->get()->each(fn(\$o) => \$sync->syncOrder(\$o));
echo 'done';
"
```

### Update / Redeploy

```bash
git pull origin main
docker compose up -d --build

# Migrasi hanya jika ada file baru
docker compose exec distribusi_obat php artisan migrate
docker compose exec auth_service php artisan migrate
docker compose exec report_service php artisan migrate
```

### Verifikasi

```bash
# Cek semua container running
docker compose ps

# Cek log jika ada error
docker compose logs --tail=50 distribusi_obat

# Verifikasi sync data
docker compose exec report_service php artisan tinker --execute="
echo 'Users: ' . App\Models\UserSnapshot::count() . PHP_EOL;
echo 'Products: ' . App\Models\ProductSnapshot::count() . PHP_EOL;
echo 'Orders: ' . App\Models\OrderSnapshot::count() . PHP_EOL;
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
- [ ] Update `APP_URL` di `distribusi_obat/.env` dengan URL ngrok terbaru
- [ ] Update webhook URL di dashboard Midtrans sandbox
- [ ] `php artisan config:clear && php artisan cache:clear` di distribusi_obat

---

## ❓ Troubleshooting

### CSS / tampilan tidak muncul saat akses via ngrok
Pastikan `APP_URL` sudah diupdate dengan URL ngrok terbaru, lalu jalankan `php artisan config:clear`.

### Webhook Midtrans tidak masuk / status order tidak berubah
- Pastikan ngrok berjalan dan URL sudah diupdate di Midtrans
- Monitor request masuk di http://127.0.0.1:4040

### Dashboard analytics error / kosong
- Pastikan `report_service` berjalan
- Pastikan `REPORT_SERVICE_URL` di `distribusi_obat` mengarah ke URL yang benar
- Jalankan initial sync jika `db_report` kosong (lihat langkah 7)

### Perubahan kode tidak terbaca setelah `git pull` di server
Opcache PHP-FPM menyimpan versi lama file. Restart PHP-FPM:
```bash
sudo systemctl restart php8.3-fpm
```

### Data di `db_report` tidak terupdate (phone, status, dll)
Sync ulang manual via tinker di `distribusi_obat`:
```bash
php artisan tinker --execute="
\$sync = app(App\Services\SyncReportService::class);
App\Models\User::all()->each(fn(\$u) => \$sync->syncUser(\$u));
echo 'done';
"
```

### `env()` return null setelah `config:cache`
Variabel yang diakses langsung via `env()` di luar file `config/` akan `null` setelah config di-cache. Pastikan semua variabel diakses via `config()`. Contoh: gunakan `config('services.report_service.url')`, bukan `env('REPORT_SERVICE_URL')` di dalam kode aplikasi.

### Error 500 tanpa detail di production
Aktifkan debug sementara di `.env`:
```env
APP_DEBUG=true
LOG_LEVEL=debug
```
Lalu cek log:
```bash
tail -50 storage/logs/laravel.log
```
Kembalikan `APP_DEBUG=false` setelah masalah ditemukan.

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

**Kelompok 10 — Proyek Akhir II**
D3 Teknologi Informasi, Institut Teknologi Del

---

© 2026 Yayasan Satriabudi Dharma Setia | SI-DOBAT E-Pharma Logistics
