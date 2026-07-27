# Backend Phase 5 - Modul Penerima MBG

Phase ini menambahkan pengelolaan data penerima MBG untuk role Admin.

## Fitur yang dibuat

- Migration `recipients`.
- Model `Recipient`.
- Relasi `Recipient` ke `Location`.
- Relasi `Location` ke banyak `Recipient`.
- Factory `RecipientFactory`.
- Controller `RecipientController`.
- Request validation `StoreRecipientRequest` dan `UpdateRecipientRequest`.
- CRUD penerima berbasis web route:
  - `GET /recipients`
  - `GET /recipients/create`
  - `POST /recipients`
  - `GET /recipients/{recipient}`
  - `GET /recipients/{recipient}/edit`
  - `PUT /recipients/{recipient}`
  - `DELETE /recipients/{recipient}`
- Validasi jumlah porsi wajib lebih dari 0.
- Validasi penerima hanya boleh terhubung ke lokasi aktif.
- Delete dibuat sebagai nonaktif agar data historis tetap aman.
- Scope `Recipient::active()` disiapkan agar modul jadwal nanti hanya memakai penerima aktif pada lokasi aktif.

## Seeder demo

Seeder menambahkan data penerima dummy pada lokasi sekolah aktif.
Data ini akan dipakai untuk menghitung total porsi pada modul jadwal distribusi.

## Akses role

Semua route `recipients` hanya boleh dibuka oleh Admin.
Petugas Distribusi dan Kepala SPPG akan mendapat HTTP 403 jika membuka halaman ini.

## Verifikasi

Jalankan:

```powershell
php artisan test
vendor\bin\pint --test
php artisan route:list
```
