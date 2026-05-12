# Deploy EZ-Store ke Oracle Cloud Free Tier dengan Docker

> Target: Oracle Cloud Ampere A1 (ARM64) · Ubuntu 22.04 · Docker Compose

---

## Daftar Isi

1. [Analisis Kesiapan Project](#1-analisis-kesiapan-project)
2. [Setup Instance Oracle Cloud](#2-setup-instance-oracle-cloud)
3. [Konfigurasi DNS & Cloudflare](#3-konfigurasi-dns--cloudflare)
4. [Install Docker di Server](#4-install-docker-di-server)
5. [Deploy Aplikasi](#5-deploy-aplikasi)
6. [Setup SSL Wildcard](#6-setup-ssl-wildcard)
7. [Verifikasi](#7-verifikasi)
8. [Update Aplikasi](#8-update-aplikasi)
9. [Yang Masih Kurang (Road Map)](#9-yang-masih-kurang-road-map)

---

## 1. Analisis Kesiapan Project

### ✅ Sudah Ada & Siap

| Fitur | Status |
|-------|--------|
| Multi-tenant (stancl/tenancy v3) | ✅ Database per tenant |
| Admin panel per toko (Filament 5) | ✅ `/admin` subdomain |
| Super-admin panel | ✅ `/super` di domain utama |
| Storefront 3 tema | ✅ Default, Boutique, Dark |
| Checkout + Midtrans | ✅ Snap + webhook |
| Transfer bank manual | ✅ |
| COD | ✅ |
| RajaOngkir shipping | ✅ Cascading province→city |
| WhatsApp notifikasi (Fonnte) | ✅ |
| Email notifikasi | ✅ 5 template |
| Manajemen produk + varian | ✅ |
| Manajemen stok | ✅ `track_stock` per varian |
| Media manager (StoreFile) | ✅ Upload + Files library |
| Export produk (Excel/PDF) | ✅ |
| Import produk (Excel) | ✅ |
| CMS halaman statis | ✅ |
| Dashboard analytics | ✅ 6 widget |
| Akun pelanggan + alamat | ✅ |
| Redis cache/session/queue | ✅ Sudah dikonfigurasi |
| Docker stack | ✅ Baru dibuat |

### 🔴 Blocker — Wajib Sebelum Jualan

#### 1. Tidak Ada Registrasi Mandiri (Self-Service Signup)
**Problem:** Saat ini calon client tidak bisa daftar sendiri. Hanya kamu yang bisa membuat toko baru via `/super` panel atau artisan command. Ini berarti kamu harus manually setup setiap client.

**Dampak bisnis:** Tidak scalable. Kalau ada 10 client daftar serentak, kamu harus manual semua.

**Solusi:** Tambah halaman daftar di landing page (`/daftar` atau `/register`) yang:
- Form: nama toko + subdomain + email + password
- Validasi subdomain unik
- Buat tenant + domain + user admin otomatis
- Kirim email selamat datang + link panel

**Estimasi waktu:** 2–3 hari kerja.

#### 2. Tidak Ada Enforcement Plan / Subscription
**Problem:** Kolom `plan` (free/starter/pro) dan `is_active` ada di database, tapi **tidak ada kode yang mengeceknya**. Jika langganan habis, toko masih jalan normal.

**Dampak bisnis:** Tidak ada cara untuk suspend toko yang tidak bayar.

**Solusi minimal:**
```php
// Middleware: CheckTenantActive.php
if (! tenant()->is_active) {
    abort(503, 'Toko ini sedang tidak aktif.');
}
```
Tambahkan middleware ini ke route tenant. Lalu super-admin bisa toggle `is_active` saat klien tidak bayar.

**Estimasi waktu:** 2 jam.

#### 3. Landing Page Belum Jadi
**Problem:** `welcome.blade.php` masih halaman default Laravel. Tidak ada:
- Harga paket (pricing page)
- Tombol "Coba Gratis" atau "Daftar Sekarang"
- Fitur unggulan

**Dampak bisnis:** Calon pembeli tidak tahu produkmu apa dan berapa harganya.

**Estimasi waktu:** 1–2 hari kerja (desain + coding).

### 🟡 Penting — Sebaiknya Ada Sebelum Launch

#### 4. Email Provider Belum Dikonfigurasi
**Problem:** `.env` default pakai `MAIL_MAILER=log` (email hanya masuk ke log file, tidak terkirim ke user).

**Solusi:** Daftar salah satu provider gratis:
- **Brevo** (ex-Sendinblue): 300 email/hari gratis ← rekomendasi
- **Mailgun**: 100 email/hari gratis
- **Resend**: 3.000 email/bulan gratis

Ganti di `.env`:
```
MAIL_MAILER=smtp
MAIL_HOST=smtp-relay.brevo.com
MAIL_PORT=587
```

#### 5. Halaman Legal Belum Ada
**Problem:** Jika menjual ke publik, kamu butuh:
- Syarat & Ketentuan
- Kebijakan Privasi (wajib jika kumpulkan data user — UU PDP Indonesia 2024)

**Solusi:** Buat dua halaman CMS (`/pages/syarat-ketentuan` dan `/pages/kebijakan-privasi`) di super-admin, isi dengan template yang relevan.

#### 6. Backup Otomatis
**Problem:** Tidak ada backup. Jika server rusak, semua data tenant hilang.

**Solusi:** Crontab di server untuk dump semua database ke Oracle Object Storage atau Google Drive:
```bash
# /etc/cron.d/ezstore-backup
0 2 * * * root /srv/ezstore/backup.sh
```

Script backup akan `mysqldump` setiap database (central + semua `tenant_*`) ke file terkompresi.

### 🟢 Nice to Have — Bisa Ditambah Bertahap

| Fitur | Dampak |
|-------|--------|
| Custom domain per toko | Client pakai domain sendiri (misal: tokosaya.com), bukan subdomain EZ-Store |
| Limit produk per plan | Paket free max 50 produk, starter max 500, dst. |
| Halaman onboarding wizard | Panduan setup toko pertama kali untuk client baru |
| Mobile app / REST API | Untuk klien yang mau pakai headless |
| Multi-user per toko | Satu toko bisa punya beberapa staff admin |
| Integrasi marketplace | Sinkronisasi ke Tokopedia/Shopee |

---

## 2. Setup Instance Oracle Cloud

### 2.1 Buat VM Instance (FREE)

1. Login ke [Oracle Cloud Console](https://cloud.oracle.com)
2. **Compute → Instances → Create Instance**
3. Konfigurasi:
   - **Name:** `ezstore-prod`
   - **Image:** Ubuntu 22.04 Minimal (atau 24.04)
   - **Shape:** `VM.Standard.A1.Flex` ← ARM, GRATIS
     - OCPU: **4** (maksimal gratis)
     - Memory: **24 GB** (maksimal gratis)
   - **Storage:** 100 GB boot volume (gratis)
   - **SSH Key:** Upload public key kamu

> Jika shape A1 tidak tersedia, coba di region lain (Singapore, Tokyo, Frankfurt). Quota A1 sering habis di region US.

### 2.2 Buka Port di Security List (PENTING)

Oracle Cloud punya firewall berlapis. Harus buka dari dua tempat:

**A. OCI Security List** (Networking → VCN → Security Lists):
Tambahkan Ingress Rules:
| Source | Protocol | Port | Keterangan |
|--------|----------|------|------------|
| 0.0.0.0/0 | TCP | 80 | HTTP |
| 0.0.0.0/0 | TCP | 443 | HTTPS |

**B. iptables di dalam server** (Oracle Ubuntu menyetel iptables by default):
```bash
# Buka port 80 dan 443
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 80 -j ACCEPT
sudo iptables -I INPUT 6 -m state --state NEW -p tcp --dport 443 -j ACCEPT

# Simpan agar persistent setelah reboot
sudo netfilter-persistent save
```

### 2.3 Catat IP Server

```bash
# Di terminal setelah SSH
curl ifconfig.me
```

Catat IP ini. Kamu akan butuhkan saat setup DNS.

---

## 3. Konfigurasi DNS & Cloudflare

### Mengapa Cloudflare?

Selain DNS gratis, Cloudflare diperlukan untuk:
- SSL wildcard otomatis via API (untuk Certbot DNS challenge)
- CDN gratis (static assets lebih cepat)
- Proteksi DDoS dasar

### 3.1 Arahkan domain ke Cloudflare

1. Daftar di [cloudflare.com](https://cloudflare.com) (gratis)
2. Add site → masukkan domain kamu (misal: `ezstore.com`)
3. Ganti nameserver domain kamu ke nameserver Cloudflare (via panel registrar)
4. Tunggu propagasi (biasanya 5–30 menit)

### 3.2 Tambah DNS Records

Di Cloudflare Dashboard → DNS → Records:

| Type | Name | IPv4 Address | Proxy |
|------|------|-------------|-------|
| A | `@` (atau `ezstore.com`) | IP Oracle Cloud kamu | ☁️ Proxied |
| A | `*` | IP Oracle Cloud kamu | ☁️ Proxied |

Record `*` (wildcard) menangkap semua subdomain: `toko1.ezstore.com`, `toko2.ezstore.com`, dst.

### 3.3 Buat API Token Cloudflare

Untuk SSL wildcard otomatis (Certbot DNS challenge):

1. Cloudflare Dashboard → kanan atas nama akun → **My Profile**
2. **API Tokens → Create Token**
3. Pilih template **Edit zone DNS**
4. Zone Resources: **Include → Specific zone → ezstore.com**
5. Copy token yang dihasilkan

Simpan token di `docker/certbot/cloudflare.ini`:
```ini
dns_cloudflare_api_token = TOKEN_KAMU_DISINI
```

---

## 4. Install Docker di Server

SSH ke server Oracle Cloud:
```bash
ssh ubuntu@IP_SERVER_KAMU
```

Install Docker:
```bash
# Install Docker Engine
curl -fsSL https://get.docker.com | sh
sudo usermod -aG docker ubuntu
newgrp docker

# Verifikasi
docker --version
docker compose version
```

---

## 5. Deploy Aplikasi

### 5.1 Clone repository

```bash
sudo mkdir -p /srv/ezstore
sudo chown ubuntu:ubuntu /srv/ezstore
cd /srv/ezstore
git clone https://github.com/KAMU/ez-store.git .
```

### 5.2 Setup environment

```bash
cp .env.docker.example .env
nano .env
```

**Hal yang WAJIB diisi:**
```env
APP_KEY=                     # Jalankan dulu: php artisan key:generate --show (lokal)
APP_URL=https://ezstore.com
CENTRAL_DOMAIN=ezstore.com
DB_PASSWORD=buat_password_acak_panjang
DB_ROOT_PASSWORD=buat_root_password_berbeda
SESSION_DOMAIN=.ezstore.com
```

> Generate APP_KEY di mesin lokal: `php artisan key:generate --show`

### 5.3 Jalankan stack (HTTP dulu, SSL belum)

Saat pertama kali, SSL belum ada. Edit `docker/nginx/default.conf` sementara untuk skip SSL (atau jalankan tanpa nginx dulu untuk test). Cara mudah:

```bash
# Jalankan semua service kecuali nginx dulu
docker compose up -d mysql redis
sleep 15  # tunggu MySQL init

# Jalankan app
docker compose up -d app queue

# Cek apakah app healthy
docker compose logs app
```

### 5.4 Setup SSL wildcard

```bash
chmod +x docker/certbot/issue.sh
./docker/certbot/issue.sh ezstore.com
```

Script ini akan:
1. Menjalankan Certbot dengan DNS challenge via Cloudflare API
2. Issue cert untuk `ezstore.com` dan `*.ezstore.com`
3. Simpan cert ke Docker volume `ssl_certs`

### 5.5 Jalankan Nginx

```bash
docker compose up -d nginx certbot
docker compose ps
```

Semua service harus `running (healthy)`.

### 5.6 Verifikasi deployment

```bash
# Cek semua container jalan
docker compose ps

# Cek log app
docker compose logs app --tail=50

# Cek log nginx
docker compose logs nginx --tail=20
```

Buka browser:
- `https://ezstore.com` → landing page
- `https://ezstore.com/super` → super-admin login

---

## 6. Setup SSL Wildcard

Proses sudah otomatis via `docker/certbot/issue.sh`. Tapi perlu dijalankan manual **sekali** di awal.

### Jika Cloudflare DNS belum propagasi

Certbot akan gagal jika DNS belum aktif. Cek dulu:
```bash
nslookup test.ezstore.com
# Harus menampilkan IP server kamu
```

### Perpanjangan Otomatis

Container `certbot` sudah dikonfigurasi untuk cek renewal setiap 12 jam. Let's Encrypt akan diperpanjang otomatis saat mendekati expiry (< 30 hari). Nginx di-reload otomatis setelah renewal.

---

## 7. Verifikasi

### Checklist lengkap

```bash
# 1. Semua container running
docker compose ps

# 2. SSL valid
curl -I https://ezstore.com

# 3. MySQL bisa create tenant database
docker compose exec mysql mysql -u root -p${DB_ROOT_PASSWORD} \
    -e "SHOW GRANTS FOR 'ezstore'@'%';"

# 4. Queue worker jalan
docker compose logs queue --tail=20

# 5. Buat tenant pertama (via artisan)
docker compose exec app php artisan tenant:create \
    demo "Toko Demo" \
    --admin-email=admin@demo.com \
    --admin-password=password123

# 6. Buka toko demo
# https://demo.ezstore.com → storefront
# https://demo.ezstore.com/admin → admin panel
```

---

## 8. Update Aplikasi

Setiap ada update kode (git push baru):

```bash
cd /srv/ezstore

# Full deploy (pull + build + restart + migrate)
./deploy.sh

# Atau manual step by step:
git pull origin main
docker compose build app queue
docker compose up -d --remove-orphans
docker compose exec app php artisan tenants:migrate --force
docker compose restart queue
```

> ⚠️ **Selalu jalankan `tenants:migrate`** setelah update yang ada migration baru. Tanpa ini, tenant lama tidak dapat skema database terbaru.

---

## 9. Yang Masih Kurang (Road Map)

Urutan prioritas untuk menjual ke klien:

### Sprint 1 — Sebelum Soft Launch (1–2 minggu)

1. **[KRITIS] Middleware `CheckTenantActive`** (2 jam)
   - Buat [app/Http/Middleware/CheckTenantActive.php](app/Http/Middleware/CheckTenantActive.php)
   - Cek `tenant()->is_active` — jika false, tampilkan halaman "toko sedang tidak aktif"
   - Daftarkan di route tenant middleware chain
   - Ini yang memungkinkan kamu suspend toko yang tidak bayar

2. **[KRITIS] Landing page + halaman harga** (2–3 hari)
   - Buat landing page proper di [resources/views/welcome.blade.php](resources/views/welcome.blade.php)
   - Tampilkan 3 paket: Free, Starter (Rp 1jt/tahun), Pro (Rp 2.5jt/tahun)
   - Tombol "Coba Gratis" arahkan ke form daftar

3. **[KRITIS] Form registrasi mandiri** (2–3 hari)
   - Buat route `GET/POST /daftar` di [routes/web.php](routes/web.php)
   - Controller buat tenant + domain + admin user sekaligus
   - Kirim email selamat datang dengan link panel

4. **[PENTING] Konfigurasi email** (30 menit)
   - Daftar Brevo gratis, ambil SMTP credentials
   - Update `.env` dengan data SMTP benar
   - Test: `docker compose exec app php artisan tinker --execute="Mail::raw('test', fn(\$m)=>\$m->to('kamu@gmail.com')->subject('test'));"`

### Sprint 2 — Setelah 5 Klien Pertama

5. **Backup otomatis harian** (1 hari)
6. **Halaman Terms of Service + Privacy Policy** (4 jam)
7. **Monitoring uptime** (Daftar UptimeRobot gratis, ping `/up` setiap 5 menit)
8. **Limit fitur per plan** (contoh: paket free max 50 produk)

### Sprint 3 — Skala > 20 Klien

9. **Custom domain** (klien pakai `toko.mereka.com` bukan subdomain kamu)
10. **Dashboard billing** (klien lihat tagihan, bayar sendiri)
11. **Multi-user per toko** (tambah staff admin)

---

*Docker stack dibuat untuk Oracle Cloud Ampere A1 (ARM64) — semua image mendukung ARM64.*
