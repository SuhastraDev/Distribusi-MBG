# Data Demo Skripsi

Seeder utama sudah menyiapkan data demo agar sistem bisa langsung dipakai untuk presentasi.

## Akun demo

Semua akun memakai password: `password`

| Role | Email |
| --- | --- |
| Admin | `admin@distribusimbg.test` |
| Kepala SPPG | `kepala@distribusimbg.test` |
| Petugas Distribusi 1 | `petugas@distribusimbg.test` |
| Petugas Distribusi 2 | `petugas2@distribusimbg.test` |

## Data yang tersedia

- 1 depot: Depot SPPG Tangga Takat 2.
- 6 lokasi sekolah dummy sekitar Palembang.
- 2 lokasi puskesmas asli (data OpenStreetMap) sekitar Palembang: Puskesmas Boom Baru dan Puskesmas Pembantu 16 Ulu Talang Banten.
- 8 penerima MBG dummy (6 sekolah + 2 puskesmas), masing-masing terhubung ke lokasinya.
- 2 petugas distribusi aktif.
- 1 jadwal dan distribusi aktif: `SCHD-DEMO-AKTIF` / `RUN-DEMO-AKTIF`.
- 1 jadwal dan distribusi selesai: `SCHD-DEMO-SELESAI` / `RUN-DEMO-SELESAI`.
- Route plan Greedy otomatis untuk distribusi aktif dan selesai.
- Data posisi petugas untuk tampilan monitoring peta.

## Cara refresh data demo

```bash
php artisan migrate:fresh --seed
```

Gunakan perintah tersebut di database lokal/demo karena akan menghapus ulang data lama lalu mengisi data demo dari awal.
