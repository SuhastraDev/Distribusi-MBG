# Backend Phase 0 - Persiapan Awal Project

Phase 0 menyiapkan fondasi awal backend untuk Sistem Monitoring Distribusi MBG.

## Status

Selesai untuk fondasi awal repo.

## Scope Phase 0

- Membuat project Laravel baru.
- Menyiapkan struktur repo backend.
- Mengatur `.env.example` agar memakai MySQL.
- Menentukan nama aplikasi `Distribusi MBG`.
- Menambahkan dokumentasi setup lokal.
- Menjalankan verifikasi dasar.

## Environment lokal

Rekomendasi:

- PHP 8.3 atau lebih baru.
- Composer.
- MySQL.
- Laragon untuk Windows.

## Konfigurasi database

Default `.env.example`:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=distribusi_mbg
DB_USERNAME=root
DB_PASSWORD=
```

Buat database lokal:

```sql
CREATE DATABASE distribusi_mbg;
```

## Cara menjalankan

```powershell
composer install
Copy-Item .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve
```

## Verifikasi Phase 0

Perintah yang perlu lolos:

```powershell
php artisan --version
php artisan test
```

## Catatan untuk Phase 1

Phase berikutnya adalah fondasi database dan role:

- Migration `roles`.
- Update `users` dengan `role_id`, `phone`, `status`, dan `last_login_at`.
- Migration `officers`.
- Seeder role Admin, Petugas Distribusi, Kepala SPPG.
- Seeder akun demo awal.
