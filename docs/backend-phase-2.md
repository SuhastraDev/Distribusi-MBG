# Backend Phase 2 - Autentikasi dan Otorisasi

Phase 2 menambahkan fondasi login multi-role untuk Admin, Petugas Distribusi, dan Kepala SPPG.

## Scope

- Login.
- Logout.
- Ubah password.
- Redirect dashboard berdasarkan role.
- Middleware role.
- Blokir user nonaktif saat login.
- Simpan `last_login_at` setelah login berhasil.
- View minimal untuk login, dashboard role, dan ubah password.
- Feature test untuk alur auth dan role access.

## Route utama

| Method | Path | Name | Keterangan |
| --- | --- | --- | --- |
| GET | `/login` | `login` | Form login |
| POST | `/login` | `login.store` | Proses login |
| POST | `/logout` | `logout` | Logout |
| GET | `/dashboard` | `dashboard` | Redirect dashboard sesuai role |
| GET | `/admin/dashboard` | `admin.dashboard` | Dashboard Admin |
| GET | `/petugas/dashboard` | `officer.dashboard` | Dashboard Petugas |
| GET | `/kepala-sppg/dashboard` | `head.dashboard` | Dashboard Kepala SPPG |
| GET | `/change-password` | `password.edit` | Form ubah password |
| PUT | `/change-password` | `password.update` | Proses ubah password |

## Aturan role

- Admin hanya boleh membuka dashboard Admin.
- Petugas hanya boleh membuka dashboard Petugas.
- Kepala SPPG hanya boleh membuka dashboard Kepala SPPG.
- User dengan `status` selain `active` tidak boleh login.

## Verifikasi

Perintah yang digunakan:

```powershell
php artisan route:list
php artisan test
vendor\bin\pint --test
```
