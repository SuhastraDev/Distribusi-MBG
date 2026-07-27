# Backend Phase 7 - Modul Distribusi Aktual

Phase 7 menambahkan modul eksekusi distribusi aktual dari jadwal distribusi yang sudah dibuat pada Phase 6.

## Ruang lingkup

- Tabel `distribution_runs` sebagai proses distribusi aktual dari satu jadwal.
- Tabel `distribution_run_destinations` sebagai status setiap tujuan dalam distribusi.
- Relasi distribusi aktual ke jadwal, petugas, lokasi tujuan, dan penerima.
- Halaman list, tambah dari jadwal, dan detail distribusi aktual.
- Aksi mulai distribusi, update status tujuan, selesaikan distribusi, dan batalkan distribusi.
- Validasi satu jadwal hanya bisa memiliki satu distribusi aktual.
- Validasi distribusi hanya bisa dibuat dari jadwal berstatus `scheduled`.
- Validasi petugas hanya bisa mengelola distribusi miliknya.
- Validasi distribusi selesai hanya jika semua tujuan sudah `delivered` atau `skipped`.
- Hitung total porsi terkirim dari tujuan berstatus `delivered`.

## Status tujuan

- `pending`: belum diproses.
- `arrived`: petugas sudah tiba di lokasi.
- `delivered`: porsi sudah diserahkan.
- `skipped`: tujuan dilewati dengan catatan.

## Catatan batasan

Phase ini belum melakukan tracking GPS realtime. Data lokasi realtime dan peta monitoring akan menjadi pondasi phase berikutnya.
