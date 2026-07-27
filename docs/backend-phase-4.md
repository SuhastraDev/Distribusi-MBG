# Backend Phase 4 - Modul Lokasi Distribusi

Phase ini menambahkan pengelolaan data lokasi distribusi untuk role Admin.

## Fitur yang dibuat

- Migration `locations`.
- Model `Location`.
- Factory `LocationFactory`.
- Controller `LocationController`.
- Request validation `StoreLocationRequest` dan `UpdateLocationRequest`.
- CRUD lokasi berbasis web route:
  - `GET /locations`
  - `GET /locations/create`
  - `POST /locations`
  - `GET /locations/{location}`
  - `GET /locations/{location}/edit`
  - `PUT /locations/{location}`
  - `DELETE /locations/{location}`
- Tipe lokasi:
  - `depot`
  - `school`
  - `other`
- Field koordinat `latitude` dan `longitude`.
- Delete dibuat sebagai nonaktif agar data historis tetap aman.
- Scope `Location::active()` disiapkan agar modul jadwal nanti hanya memilih lokasi aktif.

## Seeder demo

Seeder menambahkan:

- Depot SPPG Tangga Takat 2.
- Beberapa lokasi tujuan dummy di sekitar Palembang.

Koordinat dummy dipakai untuk kebutuhan demo rute dan peta Leaflet/OpenStreetMap di phase berikutnya.

## Akses role

Semua route `locations` hanya boleh dibuka oleh Admin.
Petugas Distribusi dan Kepala SPPG akan mendapat HTTP 403 jika membuka halaman ini.

## Verifikasi

Jalankan:

```powershell
php artisan test
vendor\bin\pint --test
php artisan route:list
```
