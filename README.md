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
├── distribusi_obat/   → Service Utama (port 8000 & 8003)
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
php artisan migrate --seed
```

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

# URL Service Report (isi dengan IP lokal kamu)
REPORT_SERVICE_URL=http://192.168.x.x:8002

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
DB_DATABASE=e-pharma   # Bisa pakai DB yang sama
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

# URL Service Utama — gunakan port 8003 (BUKAN 8000) untuk menghindari deadlock
MAIN_APP_URL=http://192.168.x.x:8003

INTERNAL_SECRET=rahasia-report-service-2024
```

> ⚠️ **Penting:** Ganti `192.168.x.x` dengan IP lokal PC kamu.
> Cara cek IP lokal: buka Command Prompt → ketik `ipconfig` → cari **IPv4 Address** di bagian **Wireless LAN adapter Wi-Fi**.

---

### 4. Menjalankan Server

Buka **4 terminal terpisah**, jalankan masing-masing command berikut:

#### Terminal 1 — Service Utama (untuk user & ngrok)
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
set PHP_CLI_SERVER_WORKERS=4
php artisan serve --host=0.0.0.0 --port=8002 --no-reload
```

#### Terminal 4 — Service Utama (khusus internal, untuk report service)
```bash
cd distribusi_obat
php artisan serve --host=0.0.0.0 --port=8003
```

> 💡 **Kenapa ada 2 instance service utama (8000 & 8003)?**
> PHP built-in server hanya bisa handle 1 request dalam satu waktu. Saat report service meminta data ke service utama (8000), service utama sedang sibuk melayani request user — terjadi **deadlock** (saling tunggu). Port 8003 adalah instance kedua service utama yang bebas digunakan khusus oleh report service.

---

### 5. Setup ngrok

ngrok dibutuhkan untuk **dua hal**:
1. **Webhook Midtrans** — agar Midtrans bisa mengirim notifikasi pembayaran ke server lokal kamu
2. **Akses dari HP/perangkat lain** — agar bisa diakses di luar `localhost`

#### Langkah setup ngrok:

**a. Login ngrok** (hanya perlu dilakukan sekali)
```bash
ngrok authtoken YOUR_TOKEN_HERE
```
Dapatkan token di: https://dashboard.ngrok.com/get-started/your-authtoken

**b. Jalankan ngrok di Terminal 5**
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
- [ ] Buka Terminal 4 → `distribusi_obat` port 8003
- [ ] Buka Terminal 5 → `ngrok http 8000`
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
- Pastikan instance kedua `distribusi_obat` berjalan di port 8003
- Pastikan `MAIN_APP_URL` di `report_service/.env` mengarah ke `192.168.x.x:8003` (bukan localhost)

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

---

## 👥 Tim Pengembang

Kelompok 10 — Proyek Akhir II
© 2026 Yayasan Satriabudi Dharma Setia | E-Pharma Logistics
