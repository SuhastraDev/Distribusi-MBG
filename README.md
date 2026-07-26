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

## Roadmap besar

- Auth multi-role: Admin, Petugas Distribusi, Kepala SPPG.
- CRUD petugas, lokasi, penerima MBG, jadwal distribusi.
- Generate rute distribusi menggunakan Algoritma Greedy.
- Monitoring distribusi dengan peta Leaflet + OpenStreetMap.
- Update status distribusi oleh petugas.
- Laporan distribusi dan Black Box Testing.
