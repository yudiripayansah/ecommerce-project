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
| Pengaturan Toko | Nama toko, rekening bank, kota asal pengiriman, Midtrans, WhatsApp |

### Panel SaaS Super Admin (`/super`)
- Daftar semua tenant (toko)
- Buat toko baru → database + migrasi jalan otomatis
- Edit nama, paket (Free / Starter / Pro), dan status aktif

### Pengaturan Per-Toko (via admin panel)
Setiap toko mengkonfigurasi milik sendiri — tidak ada yang berbagi:

| Setting | Keterangan |
|---|---|
| Informasi toko | Nama, email, telepon |
| Rekening bank | Untuk payment method Transfer Bank |
| Kota asal pengiriman | Provinsi + kota, dipakai kalkulasi ongkir RajaOngkir |
| Midtrans Client & Server Key | Uang langsung masuk ke rekening client |
| Mode Midtrans | Sandbox (testing) atau Production (live) |
| Fonnte Token | Token WhatsApp masing-masing toko |
| Nomor WA Admin | Penerima notifikasi pesanan baru |

### Integrasi Eksternal
| Layanan | Fungsi |
|---|---|
| **Midtrans** | Payment gateway — per-tenant, uang langsung ke rekening client |
| **RajaOngkir Starter** | Ongkir live berdasarkan kota asal toko & kota tujuan pembeli |
| **Fonnte** | Notifikasi WhatsApp — per-tenant token |

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
# Database central (untuk tabel tenants & domains)
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

# Domain utama SaaS (tanpa http://)
CENTRAL_DOMAIN=localhost

# Redis (untuk session & cache)
REDIS_CLIENT=predis
SESSION_DRIVER=redis
CACHE_STORE=redis

# RajaOngkir (API key untuk production; di local pakai mock)
RAJAONGKIR_API_KEY=
RAJAONGKIR_COURIERS=jne:pos:tiki

# Midtrans & Fonnte — opsional di level .env
# Lebih baik diset per-toko via Settings > Pengaturan Toko
MIDTRANS_IS_PRODUCTION=false
```

> Midtrans, Fonnte, dan kota asal pengiriman **dikonfigurasi per-toko** melalui panel admin masing-masing toko (`/admin` → Pengaturan Toko), bukan di `.env`. Nilai `.env` hanya dipakai sebagai fallback jika toko belum mengisi settings-nya.

### 3. Migrasi database central

```bash
php artisan migrate
```

Ini membuat tabel `tenants`, `domains`, `users` (super admin), `cache`, `jobs`, dan `sessions` di database central.

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

## Setup Toko untuk Client

### Langkah per client (ulangi untuk setiap client)

**Step 1 — Buat tenant via CLI**

```bash
php artisan tenant:create {subdomain} "{Nama Toko}" \
  --plan=starter \
  --admin-email=owner@email.com \
  --admin-password=passwordRahasia
```

Perintah ini otomatis:
1. Membuat record tenant di database central
2. Membuat database MySQL baru (`tenant{subdomain}`)
3. Menjalankan semua migrasi e-commerce di database tenant
4. Mendaftarkan domain (`{subdomain}.localhost` atau `{subdomain}.CENTRAL_DOMAIN`)
5. Membuat user admin pertama di dalam database tenant

**Step 2 — Tambahkan subdomain ke `/etc/hosts`** *(hanya local dev)*

```
127.0.0.1  {subdomain}.localhost
```

Di production, cukup pastikan wildcard DNS `*.domain.com` sudah mengarah ke server.

**Step 3 — Login ke admin toko**

```
http://{subdomain}.localhost:8000/admin
```

**Step 4 — Isi Pengaturan Toko**

Buka **Settings → Pengaturan Toko**, lalu isi semua section:

| Section | Yang perlu diisi |
|---|---|
| Informasi Toko | Nama toko, email, telepon |
| Rekening Bank | Tambahkan 1+ rekening untuk Transfer Bank |
| Pengiriman | Pilih provinsi & kota asal pengiriman toko |
| Midtrans | Client Key + Server Key dari akun Midtrans client |
| Notifikasi WhatsApp | Token Fonnte + nomor WA admin toko |

**Step 5 — Input produk**

- Manual: menu **Produk → New Product**
- Import massal: **Produk → Import** → download template Excel → isi → upload

**Step 6 — Test transaksi end-to-end**

1. Buka `http://{subdomain}.localhost:8000`
2. Tambah produk ke keranjang → Checkout
3. Test COD → cek pesanan masuk di admin
4. Test Transfer Bank → upload bukti → admin konfirmasi
5. Test Midtrans → gunakan [kartu test sandbox Midtrans](https://docs.midtrans.com/docs/testing-payment-on-sandbox)

---

### Contoh: setup 3 client sekaligus

```bash
# Client 1
php artisan tenant:create toko-budi "Toko Pak Budi" \
  --plan=starter \
  --admin-email=budi@tokobudi.com \
  --admin-password=passwordBudi123

# Client 2
php artisan tenant:create toko-sari "Toko Bu Sari" \
  --plan=starter \
  --admin-email=sari@tokosari.com \
  --admin-password=passwordSari456

# Client 3
php artisan tenant:create toko-andi "Toko Pak Andi" \
  --plan=pro \
  --admin-email=andi@tokoandi.com \
  --admin-password=passwordAndi789
```

Tambahkan ke `/etc/hosts`:

```
127.0.0.1  toko-budi.localhost
127.0.0.1  toko-sari.localhost
127.0.0.1  toko-andi.localhost
```

Masing-masing toko login ke admin panel mereka sendiri dan isi pengaturan toko secara mandiri. Data antar toko sepenuhnya terisolasi.

### Cek semua toko dari Super Admin

```
http://localhost:8000/super
```

Atau via CLI:

```bash
php artisan tinker --no-interaction <<'PHP'
App\Models\Tenant::with('domains')->get()->each(function ($t) {
    echo $t->id . ' | ' . $t->name . ' | ' . $t->plan . ' | '
        . ($t->domains->first()?->domain ?? '-') . "\n";
});
PHP
```

---

## Manajemen Tenant

### Buat toko via Super Admin Panel (tanpa CLI)

Buka `http://localhost:8000/super` → Login → Toko → Tambah Toko

### Re-migrate setelah ada skema baru

```bash
# Semua tenant
php artisan tenants:migrate

# Satu tenant saja
php artisan tenants:migrate --tenants=toko-budi
```

### Nonaktifkan toko sementara

Buka Super Admin → edit tenant → toggle **Aktif** → Save.

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
Setiap toko mendapat salinan penuh dari tabel berikut, sepenuhnya terisolasi:

| Tabel | Keterangan |
|---|---|
| `users` | Admin toko |
| `products`, `product_variants`, `product_images` | Katalog produk & stok |
| `categories`, `tags`, `collections` | Pengelompokan produk |
| `orders`, `order_items` | Data pesanan |
| `customers`, `customer_addresses` | Data pelanggan |
| `settings` | Semua konfigurasi toko (Midtrans key, Fonnte token, dll.) |
| `pages` | Halaman statis CMS |
| `store_files` | Media/gambar toko |
| `notifications` | Notifikasi database Filament |

### Isolasi file upload

File yang diupload (gambar produk, bukti transfer, dll.) disimpan di:

```
storage/app/public/tenants/{tenant-id}/store-files/YYYY/MM/
storage/app/public/tenants/{tenant-id}/payment-proofs/
```

File antar toko tidak tercampur.

---

## Data RajaOngkir (Lokal)

Data provinsi dan kota sudah di-cache ke file JSON agar checkout berjalan tanpa koneksi API (berguna saat development):

```
storage/app/rajaongkir/
  provinces.json       # 34 provinsi Indonesia
  cities_1.json        # Kota-kota provinsi Aceh
  ...
  cities_34.json       # Kota-kota provinsi Papua Barat Daya
```

Di environment `local`, endpoint `/shipping/cost` otomatis mengembalikan data mock tanpa hit API. Di production, kalkulasi ongkir berjalan live menggunakan kota asal yang diset per-toko.

Untuk memperbarui data kota dari API:

```bash
php artisan rajaongkir:cache
php artisan rajaongkir:cache --province=11  # satu provinsi saja
```

---

## Perintah Artisan Kustom

| Perintah | Keterangan |
|---|---|
| `php artisan tenant:create {id} "{nama}" --plan= --admin-email= --admin-password=` | Buat tenant baru lengkap dengan DB, migrasi, & admin |
| `php artisan tenants:migrate` | Jalankan migrasi baru ke semua database tenant |
| `php artisan tenants:migrate --tenants=toko-budi` | Migrasi ke satu tenant saja |
| `php artisan rajaongkir:cache` | Ambil & cache data provinsi/kota dari API RajaOngkir |
| `php artisan rajaongkir:cache --province=11` | Cache satu provinsi saja |

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
