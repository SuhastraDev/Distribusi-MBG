# Backend Phase 1 - Fondasi Database dan Role

Phase 1 menambahkan fondasi data awal untuk autentikasi multi-role dan profil petugas distribusi.

## Scope

- Tabel `roles`.
- Update tabel `users` dengan kolom role, nomor HP, status, dan waktu login terakhir.
- Tabel `officers`.
- Relasi model `Role`, `User`, dan `Officer`.
- Factory untuk `Role` dan `Officer`.
- Seeder role dan akun demo awal.
- Feature test untuk memastikan seeder dan relasi berjalan.

## Role awal

| Role | Display name | Keterangan |
| --- | --- | --- |
| `admin` | Admin | Pengelola utama sistem |
| `petugas` | Petugas Distribusi | Pelaksana distribusi di lapangan |
| `kepala_sppg` | Kepala SPPG | Pemantau distribusi dan laporan |

## Akun demo awal

| Role | Email | Password |
| --- | --- | --- |
| Admin | `admin@distribusimbg.test` | `password` |
| Kepala SPPG | `kepala@distribusimbg.test` | `password` |
| Petugas Distribusi | `petugas@distribusimbg.test` | `password` |

## Tabel utama

### `roles`

- `id`
- `name`
- `display_name`
- `created_at`
- `updated_at`

### `users`

- `id`
- `role_id`
- `name`
- `email`
- `phone`
- `status`
- `last_login_at`
- `email_verified_at`
- `password`
- `remember_token`
- `created_at`
- `updated_at`

### `officers`

- `id`
- `user_id`
- `officer_code`
- `name`
- `phone`
- `address`
- `status`
- `created_at`
- `updated_at`

## Relasi model

- `Role` has many `User`.
- `User` belongs to `Role`.
- `User` has one `Officer`.
- `Officer` belongs to `User`.

## Verifikasi

Perintah yang digunakan:

```powershell
php artisan migrate:fresh --seed --env=testing
php artisan migrate:status --env=testing
php artisan test
```

Testing environment menggunakan `.env.testing` dengan SQLite file `database/database.sqlite`, sedangkan test suite PHPUnit tetap memakai SQLite in-memory dari `phpunit.xml`.
