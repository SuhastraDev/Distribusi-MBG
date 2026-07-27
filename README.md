# Distribusi MBG

Sistem Monitoring Distribusi Makan Bergizi Gratis (MBG) menggunakan Algoritma Greedy.

Project ini dibuat untuk membantu SPPG mengelola data distribusi, menentukan urutan rute distribusi yang lebih efisien, memantau status pengiriman, dan membuat laporan distribusi.

## Tech stack

- PHP 8.3+
- Laravel 13
- MySQL
- Blade / JavaScript untuk frontend Laravel
- Leaflet.js + OpenStreetMap untuk peta gratis

## Phase 0 Backend

Phase 0 adalah fondasi awal repo backend:

- Scaffold project Laravel.
- Konfigurasi awal environment.
- Konfigurasi `.env.example` untuk MySQL.
- Dokumentasi setup lokal.
- Verifikasi dasar Artisan dan test bawaan Laravel.

Detail Phase 0 ada di [docs/backend-phase-0.md](docs/backend-phase-0.md).

## Setup lokal

Pilih salah satu opsi database:

- Laragon/XAMPP MySQL di port `3306`.
- Docker Compose MySQL di port host `3307`.

Panduan lengkap ada di [docs/mysql-setup.md](docs/mysql-setup.md).

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Jika menggunakan Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

Pastikan database MySQL `distribusi_mbg` sudah dibuat sebelum menjalankan migration.

## Panduan frontend: clone dan mulai kerja di laptop

Bagian ini untuk teman frontend yang akan mengerjakan UI di laptop masing-masing.

### 1. Kebutuhan laptop

Pastikan sudah terinstall:

- Git
- PHP 8.3 atau lebih baru
- Composer
- Node.js dan NPM
- MySQL, bisa lewat Laragon/XAMPP atau Docker

Jika memakai Laragon, aktifkan Apache/Nginx dan MySQL dari aplikasi Laragon.

### 2. Clone repository

```bash
git clone https://github.com/SuhastraDev/Distribusi-MBG.git
cd Distribusi-MBG
```

Jika repo masuk ke folder berbeda, jalankan semua command berikut dari folder hasil clone tersebut.

### 3. Install dependency backend dan frontend

```bash
composer install
npm install
```

### 4. Buat file environment

Untuk Windows PowerShell:

```powershell
Copy-Item .env.example .env
php artisan key:generate
```

Untuk Git Bash/Linux/macOS:

```bash
cp .env.example .env
php artisan key:generate
```

### 5. Atur database di `.env`

Default `.env.example` memakai MySQL Docker di port `3307`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3307
DB_DATABASE=distribusi_mbg
DB_USERNAME=distribusi_mbg
DB_PASSWORD=distribusi_mbg
```

Jika frontend memakai Laragon/XAMPP MySQL biasa, biasanya pakai port `3306` dan user `root`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=distribusi_mbg
DB_USERNAME=root
DB_PASSWORD=
```

Buat database kosong bernama `distribusi_mbg` sebelum migration dijalankan.

### 6. Isi database demo

```bash
php artisan migrate:fresh --seed
```

Command ini akan menghapus ulang tabel lokal lalu mengisi data demo untuk kebutuhan pengerjaan UI dan demo skripsi.

Akun demo:

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@distribusimbg.test` | `password` |
| Kepala SPPG | `kepala@distribusimbg.test` | `password` |
| Petugas 1 | `petugas@distribusimbg.test` | `password` |
| Petugas 2 | `petugas2@distribusimbg.test` | `password` |

Detail data demo ada di [docs/demo-data.md](docs/demo-data.md).

### 7. Jalankan aplikasi untuk kerja frontend

Buka dua terminal.

Terminal 1 untuk Laravel:

```bash
php artisan serve
```

Terminal 2 untuk Vite/frontend asset:

```bash
npm run dev
```

Buka aplikasi di:

```text
http://127.0.0.1:8000
```

### 8. Folder yang paling sering dikerjakan frontend

- `resources/views/layouts` untuk layout utama.
- `resources/views/components` untuk komponen Blade.
- `resources/views/dashboards` untuk dashboard role.
- `resources/views/officers` untuk UI petugas.
- `resources/views/locations` untuk UI lokasi.
- `resources/views/recipients` untuk UI penerima.
- `resources/views/distribution-schedules` untuk UI jadwal.
- `resources/views/distribution-runs` untuk UI distribusi aktual.
- `resources/views/route-plans` untuk UI rute dan peta.
- `resources/views/reports` untuk UI laporan.
- `resources/css/app.css` untuk styling global.
- `resources/js/app.js` untuk JavaScript frontend.
- `routes/web.php` untuk melihat nama route yang sudah tersedia.

### 9. Catatan kerja frontend

- Frontend saat ini memakai Blade Laravel, bukan Next.js/React terpisah.
- Peta menggunakan Leaflet + OpenStreetMap, jadi tidak perlu API key Google Maps.
- Jangan ubah migration/model/controller backend tanpa koordinasi dengan backend.
- Jika butuh field baru dari backend, catat dulu kebutuhan field dan endpoint-nya.
- Untuk melihat route yang tersedia:

```bash
php artisan route:list
```

- Untuk cek apakah perubahan UI merusak test backend:

```bash
php artisan test
```

### 10. Alur git untuk frontend

Sebelum mulai kerja:

```bash
git pull origin main
```

Buat branch khusus frontend:

```bash
git checkout -b frontend/nama-fitur
```

Contoh:

```bash
git checkout -b frontend/dashboard-admin
```

Setelah selesai mengerjakan satu bagian:

```bash
git status
git add .
git commit -m "feat: update dashboard admin UI"
git push origin frontend/dashboard-admin
```

Setelah itu buat Pull Request ke branch `main`.

## Roadmap besar

- Auth multi-role: Admin, Petugas Distribusi, Kepala SPPG.
- CRUD petugas, lokasi, penerima MBG, jadwal distribusi.
- Generate rute distribusi menggunakan Algoritma Greedy.
- Monitoring distribusi dengan peta Leaflet + OpenStreetMap.
- Update status distribusi oleh petugas.
- Laporan distribusi dan Black Box Testing.
