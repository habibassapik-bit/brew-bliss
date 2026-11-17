# Panduan Deployment — Brew & Bliss

Panduan ringkas menyiapkan aplikasi ke server produksi (shared hosting/VPS). Jangan pernah menaruh password, API key, atau kredensial asli di file ini maupun di repositori.

## 1. Persyaratan Server

- PHP >= 8.5 dengan ekstensi: `pdo_mysql`, `mbstring`, `openssl`, `ctype`, `json`, `gd` (unggah gambar), `zip` (export Excel), `fileinfo`, `intl`.
- Composer 2.x, Node.js 20+ & npm (untuk build asset; bisa dilakukan di mesin lokal).
- MySQL 8+ (atau MariaDB 10.4+) — sesuai rencana PRD.
- Web server: Nginx/Apache dengan document root mengarah ke `public/` (bukan root project).

## 2. Konfigurasi `.env`

Salin `.env.example` menjadi `.env` lalu sesuaikan pada server:

```ini
APP_ENV=production          # wajib
APP_DEBUG=false             # wajib — jangan pernah true di produksi
APP_URL=https://domain-anda.com
APP_KEY=                    # generate dengan: php artisan key:generate

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=brew_bliss
DB_USERNAME=<user-db>
DB_PASSWORD=<password-db>

SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
FILESYSTEM_DISK=local

LOG_CHANNEL=stack
LOG_LEVEL=warning           # debug hanya untuk lokal
```

Catatan:

- `.env` berisi kredensial dan **tidak boleh** di-commit/dipublikasikan (sudah ada di `.gitignore`).
- Semua konfigurasi toko (nama, ongkir, status buka, rekening, metode pembayaran aktif) disimpan di **tabel `settings`**, bukan `.env` — tidak perlu variabel tambahan.
- Pastikan permission folder: `storage/` dan `bootstrap/cache/` dapat ditulis oleh user PHP.

## 3. Instalasi Dependency

```bash
composer install --optimize-autoloader --no-dev
```

## 4. Build Asset Production

```bash
npm ci
npm run build        # menghasilkan public/build + manifest.json
```

Build ini wajib; tanpa manifest, Vite akan gagal memuat CSS/JS.

## 5. Migrasi Database

```bash
php artisan migrate --force
```

Migrasi bersifat aditif (membuat tabel baru); aman dijalankan ulang. Seed data awal (role `super_admin`, akun admin, kategori/produk/settings contoh) dijalankan sesuai seeder yang tersedia:

```bash
php artisan db:seed --force
```

Ganti password admin bawaan segera setelah login pertama.

## 6. Perintah Optimasi / Cache

```bash
php artisan storage:link     # publikasi gambar produk (public/storage)
php artisan optimize         # cache config + events + routes + views
php artisan config:cache     # alternatif terpisah, jika dibutuhkan
```

Ulangi `php artisan optimize` setiap kali deploy kode baru (atau jalankan `php artisan optimize:clear` lalu optimize lagi). Jangan jalankan cache config saat `.env` masih berubah-ubah.

## 7. Checklist Sebelum Aplikasi Diakses Publik

- [ ] `APP_ENV=production` dan `APP_DEBUG=false`.
- [ ] `APP_KEY` sudah di-generate di server (bukan menyalin dari lokal).
- [ ] Database produksi sudah dimigrasi dan terisi settings awal.
- [ ] `php artisan storage:link` sudah dijalankan (gambar produk tampil).
- [ ] Asset production ter-build (`public/build/manifest.json` ada).
- [ ] `php artisan optimize` sudah dijalankan.
- [ ] HTTPS aktif; `APP_URL` memakai `https://`.
- [ ] Akun admin hanya role `super_admin`; password awal sudah diganti.
- [ ] Folder `storage/` dan `bootstrap/cache/` writable, folder lain read-only.
- [ ] Verifikasi cepat: buka beranda, login admin, buat pesanan uji, cek `/admin/laporan`, lalu hapus data uji.

## Perintah Berguna

| Perintah | Fungsi |
|---|---|
| `php artisan migrate --force` | Migrasi di produksi |
| `php artisan optimize` | Cache config/routes/views |
| `php artisan optimize:clear` | Bersihkan semua cache framework |
| `php artisan storage:link` | Link storage publik |
| `php artisan queue:work` | Proses queue (jika queue digunakan; `QUEUE_CONNECTION=database`) |
