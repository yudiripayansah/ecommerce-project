# Panduan Deploy EZ-Store ke Server Produksi

> Stack: **Laravel 13 · PHP 8.3 · MySQL · Redis · Nginx · stancl/tenancy v3**

---

## Daftar Isi

1. [Spesifikasi Server yang Dibutuhkan](#1-spesifikasi-server-yang-dibutuhkan)
2. [Persiapan Awal Server](#2-persiapan-awal-server)
3. [Setup DNS & Wildcard Subdomain](#3-setup-dns--wildcard-subdomain)
4. [Deploy Aplikasi](#4-deploy-aplikasi)
5. [Konfigurasi Nginx](#5-konfigurasi-nginx)
6. [SSL dengan Certbot](#6-ssl-dengan-certbot)
7. [Setup Queue Worker (Supervisor)](#7-setup-queue-worker-supervisor)
8. [Konfigurasi .env Produksi](#8-konfigurasi-env-produksi)
9. [Checklist Final Sebelum Go-Live](#9-checklist-final-sebelum-go-live)
10. [Onboarding Client Baru](#10-onboarding-client-baru)
11. [Update Aplikasi (Deploy Ulang)](#11-update-aplikasi-deploy-ulang)
12. [Troubleshooting Umum](#12-troubleshooting-umum)

---

## 1. Spesifikasi Server yang Dibutuhkan

### Minimum
| Komponen | Spesifikasi |
|----------|------------|
| CPU | 2 vCPU |
| RAM | 2 GB |
| Storage | 20 GB SSD |
| OS | Ubuntu 22.04 LTS atau 24.04 LTS |

### Rekomendasi (untuk 10–50 tenant aktif)
| Komponen | Spesifikasi |
|----------|------------|
| CPU | 4 vCPU |
| RAM | 4 GB |
| Storage | 50 GB SSD |

### Software yang Harus Tersedia di Server
- **PHP 8.3** + ekstensi: `fpm`, `mysql`, `redis`, `gd`, `mbstring`, `xml`, `zip`, `curl`, `intl`, `fileinfo`, `bcmath`
- **MySQL 8.0+** (bukan MariaDB — stancl/tenancy membuat database baru per tenant)
- **Redis 7+** (untuk cache, session, dan queue)
- **Nginx**
- **Composer 2**
- **Node.js 20+** + npm (hanya untuk build asset saat deploy)
- **Certbot** (untuk SSL wildcard)
- **Supervisor** (untuk queue worker)

---

## 2. Persiapan Awal Server

### 2.1 Update sistem dan install dependensi

```bash
sudo apt update && sudo apt upgrade -y

# PHP 8.3
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install -y php8.3 php8.3-fpm php8.3-mysql php8.3-redis \
    php8.3-gd php8.3-mbstring php8.3-xml php8.3-zip php8.3-curl \
    php8.3-intl php8.3-fileinfo php8.3-bcmath php8.3-cli

# MySQL
sudo apt install -y mysql-server

# Redis
sudo apt install -y redis-server
sudo systemctl enable redis-server

# Nginx
sudo apt install -y nginx

# Composer
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer

# Node.js 20
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install -y nodejs

# Supervisor
sudo apt install -y supervisor

# Certbot
sudo apt install -y certbot python3-certbot-nginx
```

### 2.2 Buat user aplikasi

```bash
sudo adduser --disabled-password --gecos "" ezstore
sudo usermod -aG www-data ezstore
```

### 2.3 Setup MySQL

```bash
sudo mysql_secure_installation

sudo mysql -u root -p
```

```sql
-- Di dalam MySQL shell:
CREATE USER 'ezstore'@'localhost' IDENTIFIED BY 'GANTI_PASSWORD_KUAT';

-- Berikan hak CREATE DATABASE (dibutuhkan stancl/tenancy untuk buat DB per tenant)
GRANT ALL PRIVILEGES ON `ezstore_central`.* TO 'ezstore'@'localhost';
GRANT ALL PRIVILEGES ON `tenant_%`.* TO 'ezstore'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

> **Kenapa `tenant_%`?** stancl/tenancy secara otomatis membuat database baru dengan format `tenant_<id>` setiap kali client baru didaftarkan. User MySQL harus punya hak untuk membuat dan mengelola database-database itu.

---

## 3. Setup DNS & Wildcard Subdomain

Ini adalah bagian **terpenting** untuk multi-tenant. Setiap toko berjalan di subdomain sendiri, contoh: `tokobaju.ezstore.com`, `elektronik.ezstore.com`.

### 3.1 Di panel DNS domain kamu (Cloudflare, Niagahoster, dll.)

Tambahkan dua DNS record berikut:

| Type | Name | Value | Proxy |
|------|------|-------|-------|
| A | `@` (atau `ezstore.com`) | IP server kamu | Ya (Cloudflare) |
| A | `*` | IP server kamu | Ya (Cloudflare) |

Record `*` (wildcard) memastikan semua subdomain (`toko1.ezstore.com`, `toko2.ezstore.com`, dst.) diarahkan ke server yang sama.

### 3.2 Verifikasi propagasi DNS

Tunggu 5–30 menit, lalu cek:

```bash
nslookup toko-test.ezstore.com
# Harus menampilkan IP server kamu
```

---

## 4. Deploy Aplikasi

### 4.1 Clone repository

```bash
sudo mkdir -p /var/www/ezstore
sudo chown ezstore:www-data /var/www/ezstore

sudo -u ezstore bash
cd /var/www/ezstore
git clone https://github.com/KAMU/ez-store.git .
```

### 4.2 Install dependencies

```bash
# PHP dependencies (tanpa dev packages)
composer install --optimize-autoloader --no-dev

# Build asset frontend
npm install
npm run build
```

### 4.3 Setup file permission

```bash
sudo chown -R ezstore:www-data /var/www/ezstore
sudo find /var/www/ezstore -type f -exec chmod 644 {} \;
sudo find /var/www/ezstore -type d -exec chmod 755 {} \;
sudo chmod -R 775 /var/www/ezstore/storage
sudo chmod -R 775 /var/www/ezstore/bootstrap/cache
```

### 4.4 Konfigurasi .env

```bash
cp .env.example .env
nano .env  # edit sesuai panduan di Bagian 8
```

### 4.5 Generate key dan setup aplikasi

```bash
php artisan key:generate

# Buat symbolic link storage
php artisan storage:link

# Jalankan migrasi database CENTRAL
php artisan migrate --force

# Optimasi untuk produksi
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache
```

---

## 5. Konfigurasi Nginx

### 5.1 Buat file konfigurasi Nginx

```bash
sudo nano /etc/nginx/sites-available/ezstore
```

Isi dengan konfigurasi berikut:

```nginx
# ── Central domain (landing page / super-admin) ────────────────────────────
server {
    listen 80;
    server_name ezstore.com www.ezstore.com;
    root /var/www/ezstore/public;
    index index.php;

    # Logging
    access_log /var/log/nginx/ezstore-central.access.log;
    error_log  /var/log/nginx/ezstore-central.error.log;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}

# ── Tenant subdomains (semua toko) ─────────────────────────────────────────
server {
    listen 80;
    server_name *.ezstore.com;
    root /var/www/ezstore/public;
    index index.php;

    access_log /var/log/nginx/ezstore-tenant.access.log;
    error_log  /var/log/nginx/ezstore-tenant.error.log;

    add_header X-Frame-Options "SAMEORIGIN";
    add_header X-Content-Type-Options "nosniff";

    charset utf-8;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location = /favicon.ico { access_log off; log_not_found off; }
    location = /robots.txt  { access_log off; log_not_found off; }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_read_timeout 300;
    }

    location ~ /\.(?!well-known).* {
        deny all;
    }

    client_max_body_size 50M;
}
```

### 5.2 Aktifkan konfigurasi

```bash
sudo ln -s /etc/nginx/sites-available/ezstore /etc/nginx/sites-enabled/
sudo nginx -t          # pastikan tidak ada error
sudo systemctl reload nginx
```

---

## 6. SSL dengan Certbot

Kita butuh **wildcard SSL certificate** agar semua subdomain tenant terlindungi HTTPS.

### 6.1 Generate wildcard SSL (via DNS challenge)

```bash
sudo certbot certonly \
    --manual \
    --preferred-challenges dns \
    -d ezstore.com \
    -d "*.ezstore.com"
```

Certbot akan meminta kamu menambahkan DNS TXT record. Ikuti instruksinya, lalu setelah propagasi berhasil, tekan Enter.

**Jika pakai Cloudflare**, bisa otomatis dengan plugin:

```bash
sudo apt install -y python3-certbot-dns-cloudflare

# Buat file credentials
sudo nano /etc/letsencrypt/cloudflare.ini
# Isi: dns_cloudflare_api_token = TOKEN_CLOUDFLARE_KAMU

sudo chmod 600 /etc/letsencrypt/cloudflare.ini

sudo certbot certonly \
    --dns-cloudflare \
    --dns-cloudflare-credentials /etc/letsencrypt/cloudflare.ini \
    -d ezstore.com \
    -d "*.ezstore.com"
```

### 6.2 Update konfigurasi Nginx untuk HTTPS

Setelah cert berhasil dibuat, ubah konfigurasi Nginx (atau jalankan `sudo certbot --nginx`) untuk menambahkan SSL ke kedua server block.

---

## 7. Setup Queue Worker (Supervisor)

Queue worker **wajib berjalan** di produksi. Saat client baru didaftarkan, stancl/tenancy membuat database baru dan menjalankan semua migrasi tenant via queue job.

### 7.1 Buat konfigurasi Supervisor

```bash
sudo nano /etc/supervisor/conf.d/ezstore-worker.conf
```

```ini
[program:ezstore-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/ezstore/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=ezstore
numprocs=2
redirect_stderr=true
stdout_logfile=/var/log/ezstore-worker.log
stopwaitsecs=3600
```

### 7.2 Aktifkan

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start ezstore-worker:*
sudo supervisorctl status    # pastikan RUNNING
```

### 7.3 Update TenancyServiceProvider agar pakai queue

Buka `app/Providers/TenancyServiceProvider.php`, ubah `shouldBeQueued(false)` menjadi `true` untuk event `TenantCreated`:

```php
// Sebelum:
])->send(...)->shouldBeQueued(false),

// Sesudah (produksi):
])->send(...)->shouldBeQueued(true),
```

---

## 8. Konfigurasi .env Produksi

Berikut nilai-nilai yang **wajib** diubah dari default:

```env
APP_NAME="EZ-Store"
APP_ENV=production
APP_KEY=                    # diisi otomatis oleh php artisan key:generate
APP_DEBUG=false             # WAJIB false di produksi!
APP_URL=https://ezstore.com

# Central domain untuk tenancy
CENTRAL_DOMAIN=ezstore.com

# ── Database ────────────────────────────────────────────────────────────────
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ezstore_central  # database utama (bukan tenant)
DB_USERNAME=ezstore
DB_PASSWORD=GANTI_PASSWORD_KUAT

# ── Redis ───────────────────────────────────────────────────────────────────
REDIS_CLIENT=phpredis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# ── Cache, Session, Queue — semua pakai Redis ────────────────────────────────
CACHE_STORE=redis
SESSION_DRIVER=redis
SESSION_DOMAIN=.ezstore.com   # titik di depan = berlaku untuk semua subdomain!
QUEUE_CONNECTION=redis

# ── Filesystem ──────────────────────────────────────────────────────────────
FILESYSTEM_DISK=public        # atau 's3' jika pakai cloud storage

# ── Mail ────────────────────────────────────────────────────────────────────
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io    # ganti dengan provider email kamu
MAIL_PORT=587
MAIL_USERNAME=xxx
MAIL_PASSWORD=xxx
MAIL_FROM_ADDRESS="noreply@ezstore.com"
MAIL_FROM_NAME="EZ-Store"

# ── Midtrans ─────────────────────────────────────────────────────────────────
MIDTRANS_SERVER_KEY=Mid-server-xxxx
MIDTRANS_CLIENT_KEY=Mid-client-xxxx
MIDTRANS_IS_PRODUCTION=true

# ── RajaOngkir ───────────────────────────────────────────────────────────────
RAJAONGKIR_API_KEY=xxxx

# ── WhatsApp (Fonnte) ─────────────────────────────────────────────────────────
FONNTE_TOKEN=xxxx
```

> **SESSION_DOMAIN** harus diawali titik (`.ezstore.com`) agar session cookie yang dibuat di `toko1.ezstore.com` juga valid di subdomain lain dan tidak bentrok.

---

## 9. Checklist Final Sebelum Go-Live

```
[ ] DNS wildcard (A record untuk * dan @) sudah propagasi
[ ] SSL wildcard sudah aktif dan diverifikasi (https:// di browser tidak ada warning)
[ ] APP_DEBUG=false di .env
[ ] php artisan config:cache sudah dijalankan
[ ] php artisan route:cache sudah dijalankan
[ ] Queue worker berjalan (supervisorctl status)
[ ] Storage symlink ada: ls -la public/storage -> ../storage/app/public
[ ] MySQL user bisa CREATE DATABASE (coba login dan test)
[ ] Redis berjalan: redis-cli ping (jawab PONG)
[ ] Buka https://ezstore.com -> landing page tampil
[ ] Buka https://ezstore.com/admin -> Filament super-admin login
[ ] Daftarkan 1 tenant uji coba, cek https://uji-coba.ezstore.com/admin tampil
[ ] Cek log tidak ada error: tail -f storage/logs/laravel.log
```

---

## 10. Onboarding Client Baru

Saat ada client baru yang mau pakai EZ-Store, ikuti langkah-langkah berikut.

### Langkah 1 — Daftarkan Tenant via Super-Admin Panel

1. Login ke `https://ezstore.com/admin` (atau domain super-admin kamu)
2. Buka menu **Tenants** → klik **Create**
3. Isi:
   - **ID / Slug**: nama unik untuk subdomain, contoh `tokobaju` (huruf kecil, tanpa spasi, tanpa karakter khusus)
   - **Domain**: `tokobaju.ezstore.com`
4. Klik **Save**

Begitu disimpan, stancl/tenancy otomatis:
- Membuat database baru: `tenant_tokobaju` (atau sesuai ID)
- Menjalankan semua migrasi tenant (semua file di `database/migrations/tenant/`)
- Tenant siap diakses

> Jika queue worker berjalan (`shouldBeQueued(true)`), proses ini berjalan di background. Tunggu ~10–30 detik sebelum subdomain aktif.

### Langkah 2 — Verifikasi Tenant Aktif

```bash
# Cek apakah database tenant sudah dibuat
mysql -u ezstore -p -e "SHOW DATABASES LIKE 'tenant_%';"

# Cek log jika ada masalah
tail -f /var/log/ezstore-worker.log
tail -f storage/logs/laravel.log
```

Buka browser: `https://tokobaju.ezstore.com` → halaman toko frontend harus muncul.
Buka: `https://tokobaju.ezstore.com/admin` → Filament admin panel tenant.

### Langkah 3 — Buat Akun Admin untuk Client

Karena setiap tenant punya database sendiri, buat user admin langsung di database tenant:

```bash
php artisan tenants:run "tinker --execute=\"
  App\Models\User::create([
    'name'     => 'Admin Toko Baju',
    'email'    => 'admin@tokobaju.com',
    'password' => bcrypt('password_sementara'),
  ]);
\"" --tenants=tokobaju
```

Atau lebih mudah via artisan command khusus jika sudah dibuat:

```bash
php artisan tenant:create-admin tokobaju admin@tokobaju.com
```

### Langkah 4 — Kirim Kredensial ke Client

Informasikan ke client:
- URL admin panel: `https://tokobaju.ezstore.com/admin`
- Email: `admin@tokobaju.com`
- Password sementara: (yang dibuat di langkah 3)
- Minta client ganti password setelah login pertama

### Langkah 5 — Konfigurasi Awal Toko (Opsional, bisa dibantu)

Client login ke admin panel dan atur:
- **Pengaturan Toko** → isi nama toko, logo, favicon
- **Pengiriman** → pilih kota asal
- **Midtrans** → masukkan API key (jika pakai payment online)
- **WhatsApp** → masukkan Fonnte token
- Tambah produk, koleksi, dsb.

### Ringkasan — Checklist per Client Baru

```
[ ] Daftarkan tenant di super-admin panel (ID unik, domain subdomain)
[ ] Verifikasi database tenant terbuat: SHOW DATABASES LIKE 'tenant_%'
[ ] Buka subdomain di browser — frontend muncul
[ ] Buka /admin subdomain — Filament panel muncul
[ ] Buat akun admin untuk client
[ ] Kirim kredensial ke client
[ ] Client setting nama toko, logo, payment di admin panel
```

---

## 11. Update Aplikasi (Deploy Ulang)

Setiap ada update kode (perbaikan bug, fitur baru):

```bash
cd /var/www/ezstore

# Pull kode terbaru
git pull origin main

# Install/update dependencies
composer install --optimize-autoloader --no-dev

# Rebuild assets
npm install
npm run build

# Jalankan migrasi central
php artisan migrate --force

# Jalankan migrasi SEMUA tenant (penting!)
php artisan tenants:migrate --force

# Refresh cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# Restart queue worker
sudo supervisorctl restart ezstore-worker:*
```

> `php artisan tenants:migrate --force` menjalankan migration baru ke semua database tenant sekaligus. Ini **wajib** dijalankan setiap ada migration tenant baru.

---

## 12. Troubleshooting Umum

### Subdomain tidak bisa diakses (502 Bad Gateway)
```bash
# Cek PHP-FPM berjalan
sudo systemctl status php8.3-fpm

# Cek error Nginx
sudo tail -f /var/log/nginx/ezstore-tenant.error.log
```

### Tenant database tidak terbuat saat daftar client baru
```bash
# Cek queue worker
sudo supervisorctl status

# Cek failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all

# Atau buat database tenant manual
php artisan tenants:migrate --tenants=ID_TENANT
```

### Error "SQLSTATE: Access denied" saat buat tenant database
Pastikan MySQL user punya hak `CREATE` untuk `tenant_%`:
```sql
GRANT ALL PRIVILEGES ON `tenant_%`.* TO 'ezstore'@'localhost';
FLUSH PRIVILEGES;
```

### Session tidak bertahan antar subdomain
Pastikan `.env` memiliki:
```env
SESSION_DRIVER=redis
SESSION_DOMAIN=.ezstore.com   # titik di depan wajib ada!
```
Lalu jalankan `php artisan config:cache`.

### Upload file tidak muncul di frontend
```bash
# Cek symlink storage ada
ls -la /var/www/ezstore/public/storage

# Jika tidak ada, buat ulang
php artisan storage:link

# Cek permission
sudo chmod -R 775 /var/www/ezstore/storage
sudo chown -R ezstore:www-data /var/www/ezstore/storage
```

### Cache lama masih terbaca setelah update
```bash
php artisan cache:clear
php artisan config:clear
php artisan config:cache
```

---

*Dokumen ini dibuat untuk EZ-Store — platform SaaS toko online multi-tenant untuk UMKM Indonesia.*
