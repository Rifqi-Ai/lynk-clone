# 🚀 Setup Tutorial — Linka

Panduan lengkap untuk menjalankan **Linka** (lynk.id clone) di laptop Anda dari nol.

## 📋 Prerequisites

Pastikan laptop Anda sudah punya (versi **Ubuntu 24.04 / Noble** atau setara):

### 🎯 Quick install (Ubuntu/Debian)
Copy-paste satu baris ini untuk install SEMUA yang dibutuhkan:

```bash
sudo apt update && sudo apt install -y \
  php8.3 php8.3-cli php8.3-common \
  php8.3-mbstring php8.3-xml php8.3-curl \
  php8.3-zip php8.3-gd php8.3-sqlite3 \
  php8.3-bcmath php8.3-intl php8.3-opcache \
  php8.3-readline php8.3-fileinfo \
  composer nodejs npm git sqlite3 unzip
```

> **Catatan versi PHP**: Ganti `8.3` dengan versi Anda (`php --version` untuk cek). Untuk Ubuntu 22.04 ganti jadi `8.1`, dst.

### 🔍 Verifikasi setelah install
```bash
php --version          # Harus 8.2+
php -m | grep -iE "gd|curl|pdo_sqlite|mbstring|xml|zip|bcmath|intl"
# Harus output: bcmath, curl, fileinfo, gd, intl, mbstring, openssl, pdo_sqlite, zip, dll.
composer --version     # 2.0+
node --version         # 18+
npm --version          # 9+
git --version          # 2.0+
```

### 🪟 Windows Users
Download [Laragon](https://laragon.org/) (recommended) — sudah include PHP 8.3 + Composer + Node.js + semua extensions Laravel out of the box. **Tidak perlu setup manual**.

[XAMPP](https://www.apachefriends.org/) juga bisa, tapi harus enable extensions manual via `php.ini` (uncomment `extension=gd`, `extension=curl`, `extension=pdo_sqlite`).

### 🍎 macOS Users
```bash
brew install php composer node sqlite3
# PHP dari Homebrew sudah include semua extensions umum
```

---

### ❗ PHP Extensions yang WAJIB ada

Project ini **memerlukan** extensions berikut (untuk QR code, payment gateway, database, dll.):
- `pdo_sqlite` — database
- `mbstring` — string handling
- `xml` & `dom` — parser
- `gd` — QR code generation
- `curl` — HTTP client (Duitku payment)
- `zip` — `composer install` & package unzip
- `bcmath` — kalkulasi harga/fee
- `intl` — internationalization
- `fileinfo`, `ctype`, `tokenizer`, `iconv` — biasanya default

### 🆘 Composer error: "requires ext-gd" ?
```bash
# Opsi 1 (recommended): install ext-gd
sudo apt install php-gd     # Debian/Ubuntu generic
# atau
sudo apt install php8.3-gd  # spesifik versi

# Opsi 2: bypass (QR code tidak akan bisa generate, fitur ini error):
composer install --ignore-platform-req=ext-gd
```

### Windows Users
Download [Laragon](https://laragon.org/) (recommended) atau [XAMPP](https://www.apachefriends.org/). Laragon includes PHP + Composer + Node.js + MySQL out of the box.

**Laragon otomatis enable `gd` + `curl` extension.** Kalau pakai XAMPP manual, enable via `php.ini` (uncomment `extension=gd` dan `extension=curl`).

### macOS Users
```bash
brew install php composer node sqlite3
# GD biasanya sudah include. Cek:
php -m | grep gd
# Kalau missing:
brew install php-gd     # atau rebuild php dengan --with-gd
```

---

## 🎯 Instalasi Step-by-Step

### Step 1: Clone Repository
### Step 1: Clone Repository
```bash
git clone https://github.com/Rifqi-Ai/lynk-clone.git
cd lynk-clone
```

### Step 2: Install PHP Dependencies
```bash
composer install
```
Tunggu sampai selesai (~1-2 menit, tergantung internet).

> **❗ Error "requires ext-gd"?** Lihat [Troubleshooting](#-troubleshooting) section di bawah.

### Step 3: Install JavaScript Dependencies

```bash
npm install
```

### Step 4: Setup Environment File

```bash
# Copy template
cp .env.example .env

# Generate app key (wajib!)
php artisan key:generate
```

Edit `.env` jika perlu (default sudah cukup untuk dev). Bagian penting:

```env
APP_NAME=Linka
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=sqlite

# Email (dev: log ke file, prod: SMTP)
MAIL_MAILER=log

# Duitku (dev: mock mode — tidak bayar sungguhan)
DUITKU_PRODUCTION=false
DUITKU_API_KEY=dev_mock_key
DUITKU_MERCHANT_CODE=dev_mock_merchant

# Google OAuth (opsional, kosongkan jika tidak dipakai)
GOOGLE_CLIENT_ID=
GOOGLE_CLIENT_SECRET=

# WhatsApp (dev: log mode)
WHATSAPP_PROVIDER=log
```

### Step 5: Setup Database

```bash
# Buat file SQLite
touch database/database.sqlite

# Jalankan migration + seed demo data
php artisan migrate --seed
```

Output yang diharapkan:
```
INFO  Running migrations.
  2014_10_12_000000_create_users_table .................. DONE
  ...
  2026_06_20_061026_add_phone_to_users_table ........... DONE

INFO  Seeding database.
  DemoSeeder .......................................... DONE
```

### Step 6: Build Frontend Assets

```bash
# Development build (dengan hot reload)
npm run dev

# ATAU production build (1x, lebih cepat saat serving)
npm run build
```

### Step 7: Jalankan Server

```bash
php artisan serve
```

Output:
```
INFO  Server running on [http://127.0.0.1:8000]
Press Ctrl+C to stop the server
```

**Buka browser:** http://127.0.0.1:8000

---

## 🧪 Testing — Akun Demo

Setelah `php artisan migrate --seed`, login dengan:

| Username | Email | Password | Tier |
|---|---|---|---|
| `demo_alice` | `alice@demo.linka.id` | `password123` | Pro |
| `demo_bob` | `bob@demo.linka.id` | `password123` | Pro |
| `demo_charlie` | `charlie@demo.linka.id` | `password123` | Starter |
| `demo_diana` | `diana@demo.linka.id` | `password123` | Starter |
| `demo_eko` | `eko@demo.linka.id` | `password123` | Pro |

**Sample data:** 5 creators, 24 products (semua 7 types), 8 paid orders, 3 event tickets, 6+2 course modules, 2 vouchers.

### Test 7 Product Types

Login sebagai `demo_alice` lalu buka:
- http://127.0.0.1:8000/demo_alice/pa0f3ocs9ivt — Digital
- http://127.0.0.1:8000/demo_alice/2fl0y239y6np — Appointment
- http://127.0.0.1:8000/demo_alice/9ogwxwvxi7f7 — Donation

Login sebagai `demo_bob`:
- http://127.0.0.1:8000/demo_bob/3zwqhd7wljks — Event (punya ticket buyer!)

Login sebagai `demo_diana`:
- http://127.0.0.1:8000/demo_diana/tszv4ily386v — Course (punya course buyer!)
- http://127.0.0.1:8000/demo_diana/oyo3eumtzccx — Blog (paywalled)

Login sebagai `demo_eko`:
- http://127.0.0.1:8000/demo_eko/temsa2106jpb — Physical (punya shipping!)

---

## 🎬 Testing — User Flow

### A. Sebagai Buyer (tanpa login)

1. **Buka landing:** http://127.0.0.1:8000
2. **Lihat pricing:** http://127.0.0.1:8000/pricing
3. **Browse creator:** http://127.0.0.1:8000/demo_alice
4. **Lihat produk:** klik salah satu produk
5. **Beli:** klik "Buy now" → isi email → klik "Pay" → **Duitku mock** akan redirect ke payment page (tidak bayar sungguhan, langsung success)
6. **Cek email:** Lihat `storage/logs/laravel.log` — email konfirmasi ditulis ke file
7. **Cek WhatsApp log:** Sama, lihat file log

### B. Sebagai Creator (login sebagai demo_alice)

1. **Login:** http://127.0.0.1:8000/login
   - Email: `alice@demo.linka.id`
   - Password: `password123`
2. **Dashboard:** http://127.0.0.1:8000/dashboard
   - Lihat stats (Products/Sales/Revenue/Views)
   - Lihat chart revenue + sales 30 hari
   - Lihat top products + sales by type
3. **Create product:** klik "+ New" atau http://127.0.0.1:8000/dashboard/products/create
   - Pilih type (digital/course/event/etc)
   - Isi title, price, description
   - (Optional) Upload thumbnail
4. **Manage shipping:** http://127.0.0.1:8000/dashboard/fulfillment
   - Lihat orders yang perlu dikirim
   - Update status (pending → packed → shipped → delivered)
5. **Event check-in:** http://127.0.0.1:8000/dashboard/events/demo_bob/3zwqhd7wljks/checkin
   - Lihat attendee list
   - Input ticket code → check-in
6. **Profile settings:** http://127.0.0.1:8000/settings/profile
   - Update bio, title, avatar
   - Tambah nomor WhatsApp untuk notifikasi

### C. Test Cart + Voucher

1. Buka http://127.0.0.1:8000/demo_alice
2. Klik "Add to cart" di beberapa produk
3. Buka http://127.0.0.1:8000/demo_alice/cart
4. Apply voucher `WELCOME10` (10% off)
5. Checkout → pay

### D. Test Search + Filter

1. Buka http://127.0.0.1:8000/demo_alice
2. Ketik "preset" di search box → Enter
3. Klik filter "📦 Digital Product" atau "📅 Appointment"
4. Ubah sort ke "Harga ↑" atau "Terpopuler"

---

## 🐛 Troubleshooting

### ❌ `requires ext-gd` (saat `composer install`)
```bash
# Ubuntu/Debian — install GD extension
sudo apt install php-gd          # generic
sudo apt install php8.3-gd       # spesifik versi (sesuaikan dengan php --version)

# Windows (Laragon) — biasanya sudah include, restart Laragon
# Windows (XAMPP) — edit php.ini, uncomment:  extension=gd

# macOS (Homebrew) — biasanya sudah include. Kalau tidak:
brew install php-gd
# atau rebuild: brew reinstall php --with-gd

# Verify:
php -m | grep gd
```

### ❌ `could not find driver` (saat `migrate` / `db`)
Driver PHP untuk database tidak terinstall.
```bash
# SQLite (default project ini)
sudo apt install php8.3-sqlite3    # sesuaikan versi PHP Anda

# MySQL (kalau ganti DB)
sudo apt install php8.3-mysql
# Edit .env: DB_CONNECTION=mysql + DB_HOST, DB_DATABASE, DB_USERNAME, DB_PASSWORD

# PostgreSQL
sudo apt install php8.3-pgsql

# Verify driver terinstall:
php -m | grep -iE "pdo|sqlite|mysql|pgsql"
# Harus ada: PDO, pdo_sqlite (atau pdo_mysql / pdo_pgsql)
```

### ❌ "Please provide a valid cache path" (saat buka web)
Folder `storage/framework/` belum dibuat — biasanya karena `git clone` skip empty dirs.
```bash
cd ~/lynk-clone
mkdir -p storage/framework/cache storage/framework/sessions \
         storage/framework/views storage/framework/testing \
         storage/logs bootstrap/cache
chmod -R 775 storage bootstrap/cache
php artisan optimize:clear
```
> **Note**: Update terbaru project ini sudah include `.gitkeep` files + `post-autoload-dump` composer script yang auto-create folders, jadi issue ini seharusnya tidak terjadi lagi. Tapi kalau sempat, jalankan command di atas.

### ❌ `SQLSTATE: database not found`
```bash
# Buat file SQLite
touch database/database.sqlite
php artisan migrate:fresh --seed
```

### ❌ `vite: command not found`
```bash
npm install
```

### ❌ `Class "SimpleSoftwareIO\\QrCode" not found`
```bash
composer install
php artisan clear-compiled
```

### ❌ Port 8000 already in use
```bash
# Ganti port
php artisan serve --port=8001
```

### ❌ Email tidak terkirim
Email dev mode masuk ke `storage/logs/laravel.log`. Cek file:
```bash
tail -f storage/logs/laravel.log
```

### ❌ Permission denied di storage
```bash
chmod -R 775 storage bootstrap/cache
```

### ❌ npm install lambat / gagal
```bash
# Clear cache
npm cache clean --force
rm -rf node_modules package-lock.json
npm install
```

### ❌ Course tidak bisa diakses setelah beli
Cek `storage/logs/laravel.log` untuk error. Token harus di-generate dengan `APP_KEY` yang sama persis dengan yang ada di `.env`.

---

## 🔄 Update dari GitHub

```bash
# Tarik update terbaru
git pull origin main

# Update dependencies
composer install
npm install

# Jalankan migration baru (jika ada)
php artisan migrate

# Rebuild assets
npm run build

# Clear cache
php artisan config:clear
php artisan route:clear
php artisan view:clear
```

---

## 📦 Untuk Production Deployment

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_DATABASE=linka
DB_USERNAME=linka_user
DB_PASSWORD=strong_password

# Email
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailgun.org
MAIL_PORT=587
MAIL_USERNAME=...
MAIL_PASSWORD=...
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=hello@yourdomain.com
MAIL_FROM_NAME="${APP_NAME}"

# Duitku (real keys)
DUITKU_PRODUCTION=true
DUITKU_API_KEY=<your-real-key>
DUITKU_MERCHANT_CODE=<your-merchant-code>

# WhatsApp
WHATSAPP_PROVIDER=wablas
WHATSAPP_API_KEY=<your-key>
WHATSAPP_SENDER=6281234567890
WHATSAPP_PRODUCTION=true
```

```bash
# Optimize
php artisan config:cache
php artisan route:cache
php artisan view:cache
npm run build

# Set permissions
chmod -R 775 storage bootstrap/cache
```

---

## 🆘 Butuh Bantuan?

1. Cek `storage/logs/laravel.log` untuk error detail
2. Jalankan `php artisan optimize:clear` untuk clear semua cache
3. Buka issue di GitHub repo Anda
4. Tanya komunitas Laravel di [laravel.com](https://laravel.com)

---

**Happy coding! 🚀**
