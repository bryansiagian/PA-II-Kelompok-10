# 🏥 E-Pharma Logistics — Panduan Menjalankan di Lokal

Panduan lengkap untuk menjalankan proyek E-Pharma di komputer lokal, termasuk setup server, ngrok, dan pembayaran Midtrans.

---

## 📋 Prasyarat

Pastikan semua software berikut sudah terinstall di komputer kamu:

| Software | Versi Minimum | Unduh |
|----------|--------------|-------|
| PHP | 8.2 | https://www.php.net/downloads |
| Composer | 2.x | https://getcomposer.org |
| MySQL | 8.0 | https://dev.mysql.com/downloads |
| Node.js | 18.x | https://nodejs.org |
| ngrok | 3.x | https://ngrok.com/download |
| Git | terbaru | https://git-scm.com |

---

## 📁 Struktur Proyek

Proyek ini terdiri dari **3 service** yang berjalan bersamaan:

```
PA-II-Kelompok-10/
├── distribusi_obat/   → Service Utama (port 8000)
├── auth_service/      → Service Autentikasi (port 8001)
└── report_service/    → Service Laporan (port 8002)
```

---

## 🚀 Langkah-Langkah Setup

### 1. Clone Repository

```bash
git clone https://github.com/PA-II-Kelompok-10/distribusi_obat.git
cd PA-II-Kelompok-10
```

---

### 2. Setup Setiap Service

Lakukan langkah berikut untuk **masing-masing** folder (`distribusi_obat`, `auth_service`, `report_service`):

```bash
# Masuk ke folder service
cd distribusi_obat   # (ulangi untuk auth_service dan report_service)

# Install dependencies PHP
composer install

# Salin file konfigurasi
cp .env.example .env

# Generate application key
php artisan key:generate

# Jalankan migrasi database
php artisan migrate --seed   # distribusi_obat & auth_service
php artisan migrate          # report_service (tidak perlu seed)
```

> 💡 **report_service** tidak perlu `--seed` karena datanya diisi otomatis via sync dari service utama.

---

### 3. Konfigurasi File `.env`

#### 📌 `distribusi_obat/.env`

```env
APP_NAME=Laravel
APP_ENV=local
APP_URL=http://localhost:8000   # Akan diubah saat pakai ngrok

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=e-pharma
DB_USERNAME=root
DB_PASSWORD=

# Midtrans (daftar di https://sandbox.midtrans.com)
MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxx
MIDTRANS_IS_PRODUCTION=false
MIDTRANS_IS_SANITIZED=true
MIDTRANS_IS_3DS=true

# URL Service Report
REPORT_SERVICE_URL=http://127.0.0.1:8002

INTERNAL_SECRET=rahasia-report-service-2024
```

#### 📌 `auth_service/.env`

```env
APP_NAME=AuthService
APP_ENV=local
APP_URL=http://localhost:8001

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_auth
DB_USERNAME=root
DB_PASSWORD=
```

#### 📌 `report_service/.env`

```env
APP_NAME=ReportService
APP_ENV=local
APP_URL=http://localhost:8002

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_report
DB_USERNAME=root
DB_PASSWORD=

INTERNAL_SECRET=rahasia-report-service-2024
```

> ℹ️ `MAIN_APP_URL` sudah tidak diperlukan lagi di report_service. Service ini kini memiliki database snapshot sendiri (db_report) dan tidak perlu memanggil balik ke service utama.

---

### 4. Menjalankan Server

Buka **3 terminal terpisah**, jalankan masing-masing command berikut:

#### Terminal 1 — Service Utama
```bash
cd distribusi_obat
php artisan serve --host=0.0.0.0 --port=8000
```

#### Terminal 2 — Auth Service
```bash
cd auth_service
php artisan serve --host=0.0.0.0 --port=8001
```

#### Terminal 3 — Report Service
```bash
cd report_service
php artisan serve --host=0.0.0.0 --port=8002
```

> ✅ **Sebelumnya dibutuhkan 4 terminal** (port 8000 & 8003 untuk distribusi_obat) karena report_service memanggil balik ke service utama — menyebabkan deadlock. Setelah refactor arsitektur microservice, report_service kini memiliki database sendiri (db_report) sehingga **port 8003 tidak diperlukan lagi**.

---

### 5. Initial Sync Data (Pertama Kali / Setelah migrate:fresh)

Setelah migrate, jalankan sync awal agar db_report terisi dengan data yang sudah ada:

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

> Setelah ini, db_report akan otomatis terupdate setiap kali ada transaksi baru di service utama.

---

### 6. Setup ngrok

ngrok dibutuhkan untuk **dua hal**:
1. **Webhook Midtrans** — agar Midtrans bisa mengirim notifikasi pembayaran ke server lokal kamu
2. **Akses dari HP/perangkat lain** — agar bisa diakses di luar `localhost`

#### Langkah setup ngrok:

**a. Login ngrok** (hanya perlu dilakukan sekali)
```bash
ngrok authtoken YOUR_TOKEN_HERE
```
Dapatkan token di: https://dashboard.ngrok.com/get-started/your-authtoken

**b. Jalankan ngrok di Terminal 4**
```bash
ngrok http 8000
```

**c. Salin URL ngrok** yang muncul, contoh:
```
Forwarding  https://xxxx-xxxx.ngrok-free.app -> http://localhost:8000
```

**d. Update `APP_URL` di `distribusi_obat/.env`**
```env
APP_URL=https://xxxx-xxxx.ngrok-free.app
```

Lalu bersihkan cache:
```bash
cd distribusi_obat
php artisan config:clear
php artisan cache:clear
```

**e. Daftarkan URL webhook ke Midtrans**
1. Login ke https://dashboard.sandbox.midtrans.com
2. Buka **Settings → Configuration**
3. Isi **Payment Notification URL** dengan:
   ```
   https://xxxx-xxxx.ngrok-free.app/api/payment/webhook
   ```
4. Klik **Save**

> ⚠️ **Perhatian:** URL ngrok berubah setiap kali ngrok dimatikan dan dinyalakan ulang (kecuali akun berbayar). Setiap sesi development baru, ulangi langkah d dan e.

---

## 💳 Setup Pembayaran Midtrans (Sandbox)

### Mendaftar Akun Sandbox

1. Buka https://dashboard.sandbox.midtrans.com
2. Daftar akun baru (gratis)
3. Setelah login, buka **Settings → Access Keys**
4. Salin **Server Key** dan **Client Key**
5. Masukkan ke `distribusi_obat/.env`:
   ```env
   MIDTRANS_SERVER_KEY=Mid-server-xxxxxxxxxxxxxxxx
   MIDTRANS_CLIENT_KEY=Mid-client-xxxxxxxxxxxxxxxx
   ```

### Testing Pembayaran

Gunakan data kartu kredit test berikut (jangan pakai kartu asli):

| Field | Nilai |
|-------|-------|
| Nomor Kartu | 4811 1111 1111 1114 |
| Expiry | 01/39 |
| CVV | 123 |
| OTP/3DS | 112233 |

> 💡 Untuk metode QRIS, tidak bisa disimulasikan langsung dari dashboard Midtrans sandbox. Gunakan kartu kredit test untuk pengujian.

### Alur Pembayaran

```
Customer checkout
      ↓
Snap popup Midtrans muncul
      ↓
Customer bayar (pakai kartu test)
      ↓
Midtrans kirim webhook ke URL ngrok
      ↓
Status order otomatis berubah: Awaiting Payment → Pending (Lunas)
      ↓
Admin bisa menyetujui pesanan
```

---

## 🔁 Checklist Setiap Kali Mulai Development

Setiap kali membuka proyek, pastikan urutan ini dijalankan:

- [ ] Jalankan MySQL / XAMPP
- [ ] Buka Terminal 1 → `distribusi_obat` port 8000
- [ ] Buka Terminal 2 → `auth_service` port 8001
- [ ] Buka Terminal 3 → `report_service` port 8002
- [ ] Buka Terminal 4 → `ngrok http 8000`
- [ ] Update `APP_URL` di `.env` dengan URL ngrok terbaru
- [ ] Update URL webhook di dashboard Midtrans sandbox
- [ ] Jalankan `php artisan config:clear && php artisan cache:clear`

---

## ❓ Troubleshooting

### CSS tidak muncul saat akses via ngrok
Pastikan `APP_URL` di `.env` sudah diupdate dengan URL ngrok terbaru dan sudah menjalankan `php artisan config:clear`.

### Webhook Midtrans tidak masuk (status order tidak berubah setelah bayar)
- Pastikan ngrok sedang berjalan
- Pastikan URL webhook di dashboard Midtrans sudah diupdate dengan URL ngrok terbaru
- Cek di http://127.0.0.1:4040 apakah ada request masuk dengan status 200

### Dashboard analytics error 503
- Pastikan `report_service` berjalan di port 8002
- Pastikan `REPORT_SERVICE_URL` di `distribusi_obat/.env` mengarah ke `http://127.0.0.1:8002`
- Pastikan sudah menjalankan initial sync via tinker setelah migrate

### Error "Class Midtrans\Config not found"
```bash
cd distribusi_obat
composer require midtrans/midtrans-php
php artisan config:clear
```

### Error "No query results for model ProductOrderStatus"
Database belum di-seed. Jalankan:
```bash
php artisan db:seed
```

### db_report kosong / analytics tidak ada data
Jalankan ulang initial sync via tinker di distribusi_obat (lihat langkah 5).

---

## 👥 Tim Pengembang

Kelompok 10 — Proyek Akhir II
© 2026 Yayasan Satriabudi Dharma Setia | E-Pharma Logistics
