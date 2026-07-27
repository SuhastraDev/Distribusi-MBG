# Backend Phase 3 - Modul Petugas

Phase ini menambahkan pengelolaan data Petugas Distribusi untuk role Admin.

## Fitur yang dibuat

- Route resource `officers`.
- Controller `OfficerController`.
- Request validation `StoreOfficerRequest` dan `UpdateOfficerRequest`.
- CRUD petugas berbasis web route:
  - `GET /officers`
  - `GET /officers/create`
  - `POST /officers`
  - `GET /officers/{officer}`
  - `GET /officers/{officer}/edit`
  - `PUT /officers/{officer}`
  - `DELETE /officers/{officer}`
- Saat petugas dibuat, sistem otomatis membuat akun `users` dengan role `petugas`.
- Email user dan kode petugas divalidasi unik.
- Delete dibuat sebagai nonaktif agar riwayat distribusi tetap aman.
- Scope `Officer::active()` disiapkan agar modul jadwal nanti hanya memilih petugas aktif.

## Akses role

Semua route `officers` hanya boleh dibuka oleh Admin.
Petugas Distribusi dan Kepala SPPG akan mendapat HTTP 403 jika membuka halaman ini.

## Catatan implementasi

Status petugas disimpan di dua tempat:

- `officers.status`
- `users.status`

Keduanya dibuat sinkron saat create, update, dan deactivate.

## Verifikasi

Jalankan:

```powershell
php artisan test
vendor\bin\pint --test
php artisan route:list
```
