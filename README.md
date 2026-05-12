# EZ-Store

Platform e-commerce multi-tenant berbasis SaaS untuk UMKM Indonesia. Setiap toko berjalan di subdomain sendiri dengan database yang sepenuhnya terisolasi.

**Stack:** Laravel 13 · Filament 5 · MySQL · Redis · stancl/tenancy

---

## Arsitektur

```
ezstore.id/super          → Panel SaaS (kelola semua toko)
toko-abc.ezstore.id       → Storefront toko ABC
toko-abc.ezstore.id/admin → Panel admin toko ABC (Filament)
```

Setiap tenant mendapat database MySQL tersendiri (`tenanttoko-abc`) yang di-migrate otomatis saat tenant dibuat. Tidak ada data yang tercampur antar toko.

---

## Fitur

### Storefront (Frontend)
- Halaman beranda dengan hero, produk unggulan, dan koleksi
- Halaman produk dengan pilihan varian (ukuran, warna, dll.)
- Pencarian produk
- Koleksi / kategori produk
- Keranjang belanja (session-based)
- Checkout dengan:
  - Dropdown provinsi & kota via **RajaOngkir** (cascading)
  - Kalkulasi ongkir live (JNE, POS, TIKI)
  - Pilihan pembayaran: Transfer Bank, COD, atau Midtrans (kartu kredit, GoPay, dll.)
  - Upload bukti transfer
- Halaman sukses pesanan dengan rincian subtotal + ongkir + total
- Akun pelanggan: profil, daftar pesanan, manajemen alamat

### Panel Admin Toko (`/admin`)
| Menu | Keterangan |
|---|---|
| Dashboard | Statistik penjualan, grafik, produk terlaris, stok rendah |
| Produk | CRUD produk, varian, gambar, tracking stok, ekspor Excel/PDF |
| Kategori & Tag | Pengelompokan produk |
| Koleksi | Kurasi produk ke dalam koleksi |
| Pesanan | Daftar pesanan, update status, input nomor resi |
| Pelanggan | Profil pelanggan, riwayat pesanan, alamat tersimpan |
| File | Manajemen file/gambar toko |
| Halaman | CMS halaman statis |

### Panel SaaS Super Admin (`/super`)
- Daftar semua tenant (toko)
- Buat toko baru → database + migrasi jalan otomatis
- Edit nama, paket (Free / Starter / Pro), dan status aktif

### Integrasi Eksternal
| Layanan | Fungsi |
|---|---|
| **Midtrans** | Payment gateway (redirect & notification webhook) |
| **RajaOngkir Starter** | Ongkir live berdasarkan berat & kota tujuan |
| **Fonnte** | Notifikasi WhatsApp ke pelanggan & admin |

### Notifikasi
- **Email**: Pesanan baru, konfirmasi pembayaran, update status (template Mailable per kejadian)
- **WhatsApp** (via Fonnte): Pesanan baru, pembayaran dikonfirmasi, pesanan dikirim (lengkap dengan nomor resi)

---

## Cara Install

### Prasyarat
- PHP 8.3+
- MySQL 8+
- Redis
- Composer
- Node.js & npm

### 1. Clone & install dependensi

```bash
git clone <repo-url> ez-store
cd ez-store
composer install
npm install && npm run build
```

### 2. Konfigurasi environment

```bash
cp .env.example .env
php artisan key:generate
```

Edit `.env` — bagian wajib diisi:

```env
# Database (central — untuk tabel tenants & domains)
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

# Domain utama SaaS (tanpa http://)
CENTRAL_DOMAIN=localhost

# Redis (untuk session & cache)
REDIS_CLIENT=predis
SESSION_DRIVER=redis
CACHE_STORE=redis

# Midtrans
MIDTRANS_SERVER_KEY=
MIDTRANS_CLIENT_KEY=
MIDTRANS_IS_PRODUCTION=false

# RajaOngkir
RAJAONGKIR_API_KEY=
RAJAONGKIR_ORIGIN_CITY_ID=501   # ID kota asal pengiriman (501 = Surabaya)
RAJAONGKIR_COURIERS=jne:pos:tiki

# WhatsApp via Fonnte
FONNTE_TOKEN=
FONNTE_ADMIN_WHATSAPP=628xxxxxxxxxx
```

### 3. Migrasi database central

```bash
# Buat tabel tenants & domains di database central
php artisan migrate --path=database/migrations/2019_09_15_000010_create_tenants_table.php
php artisan migrate --path=database/migrations/2019_09_15_000020_create_domains_table.php

# Tabel bawaan Laravel (users untuk super admin, cache, jobs)
php artisan migrate
```

### 4. Buat akun super admin SaaS

```bash
php artisan make:filament-user
# Masukkan nama, email, password
# Akses: http://localhost:8000/super
```

### 5. Jalankan server

```bash
php artisan serve
# Buka http://localhost:8000/super
```

---

## Manajemen Tenant

### Buat toko baru via CLI

```bash
php artisan tenant:create {id} "{Nama Toko}" \
  --plan=starter \
  --admin-email=owner@toko.com \
  --admin-password=rahasia
```

Contoh:

```bash
php artisan tenant:create toko-budi "Toko Pak Budi" \
  --plan=free \
  --admin-email=budi@toko.com \
  --admin-password=password123
```

Perintah ini secara otomatis:
1. Membuat record tenant di database central
2. Membuat database MySQL baru (`tenanttoko-budi`)
3. Menjalankan semua migrasi e-commerce di database tenant
4. Mendaftarkan domain (`toko-budi.localhost`)
5. Membuat user admin di dalam database tenant

### Buat toko baru via Super Admin Panel

Buka `http://localhost:8000/super` → Login → Toko → Tambah Toko

### Testing di lokal (subdomain)

Tambahkan entri ke `/etc/hosts`:

```
127.0.0.1  toko-budi.localhost
```

Kemudian akses:
- Storefront: `http://toko-budi.localhost:8000`
- Admin toko: `http://toko-budi.localhost:8000/admin`

### Re-migrate semua tenant (setelah ada migrasi baru)

```bash
php artisan tenants:migrate
```

---

## Struktur Database

### Database Central (`ecommerce`)
| Tabel | Keterangan |
|---|---|
| `users` | Akun super admin SaaS |
| `tenants` | Daftar toko (id, name, plan, is_active) |
| `domains` | Domain per toko (misal: `toko-budi.localhost`) |
| `cache`, `jobs`, `sessions` | Bawaan Laravel |

### Database Tenant (`tenant{id}`)
Setiap toko mendapat salinan penuh dari tabel berikut:

| Tabel | Keterangan |
|---|---|
| `users` | Admin toko |
| `products`, `product_variants`, `product_images` | Katalog produk & stok |
| `categories`, `tags`, `collections` | Pengelompokan produk |
| `orders`, `order_items` | Data pesanan |
| `customers`, `customer_addresses` | Data pelanggan |
| `settings` | Konfigurasi toko (nama, logo, dll.) |
| `pages` | Halaman statis CMS |
| `store_files` | Media/gambar toko |
| `notifications` | Notifikasi database Filament |

---

## Data RajaOngkir (Lokal)

Agar checkout tetap berjalan tanpa koneksi ke API RajaOngkir (berguna saat development), data provinsi dan kota sudah di-cache ke file JSON lokal:

```
storage/app/rajaongkir/
  provinces.json          # 34 provinsi Indonesia
  cities_1.json           # Kota-kota di provinsi 1 (Aceh)
  cities_2.json           # dst.
  ...
  cities_34.json
```

Di environment `local`, endpoint `/shipping/cost` mengembalikan data mock (tidak hit API). Di production, kalkulasi ongkir berjalan live.

Untuk memperbarui data kota dari API (production):

```bash
php artisan rajaongkir:cache
php artisan rajaongkir:cache --province=11  # hanya satu provinsi
```

---

## Perintah Artisan Kustom

| Perintah | Keterangan |
|---|---|
| `php artisan tenant:create` | Buat tenant baru lengkap dengan DB & admin |
| `php artisan rajaongkir:cache` | Ambil & cache data provinsi/kota dari API RajaOngkir |
| `php artisan tenants:migrate` | Jalankan migrasi baru ke semua database tenant |
| `php artisan tenants:migrate --tenants=toko-budi` | Migrasi ke satu tenant saja |

---

## Teknologi yang Digunakan

| Paket | Versi | Fungsi |
|---|---|---|
| `laravel/framework` | ^13.0 | Core framework |
| `filament/filament` | ^5.5 | Admin panel (tenant & super) |
| `stancl/tenancy` | ^3.10 | Multi-tenant (database per tenant) |
| `midtrans/midtrans-php` | ^2.6 | Payment gateway |
| `maatwebsite/excel` | ^3.1 | Export produk ke Excel |
| `barryvdh/laravel-dompdf` | ^3.1 | Export produk ke PDF |
| `predis/predis` | ^3.4 | Redis client (PHP murni, tanpa ekstensi phpredis) |

---

## Lisensi

Proprietary — 360&5
