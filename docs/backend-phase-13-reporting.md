# Backend Phase 13 - Modul Laporan

Phase ini melengkapi modul laporan distribusi MBG untuk kebutuhan Admin dan Kepala SPPG.

## Fitur

- Halaman laporan distribusi: `/reports/distributions`.
- Filter laporan berdasarkan tanggal awal, tanggal akhir, petugas, dan status.
- Detail laporan per distribusi: `/reports/distributions/{distribution_run}`.
- Ringkasan total distribusi, tujuan, porsi, jarak rute, estimasi waktu, dan waktu aktual.
- Detail tujuan distribusi berisi lokasi, penerima, status, porsi, waktu tiba, waktu selesai, dan catatan.
- Timeline status sederhana berdasarkan waktu distribusi dan status tujuan.
- Export CSV: `/reports/distributions/export`.
- Export Excel ringan: `/reports/distributions/export-excel`.

## Hak akses

- Admin: bisa melihat laporan, detail laporan, export CSV, dan export Excel.
- Kepala SPPG: bisa melihat laporan, detail laporan, export CSV, dan export Excel.
- Petugas Distribusi: tidak bisa membuka laporan global.

## Catatan implementasi

Export Excel dibuat sebagai file `.xls` berbasis tabel HTML agar tidak menambah dependency eksternal. Format ini cukup untuk demo dan dapat dibuka di aplikasi spreadsheet umum.
