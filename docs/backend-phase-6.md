# Backend Phase 6 - Modul Jadwal Distribusi

Phase 6 menambahkan modul perencanaan jadwal distribusi MBG. Modul ini dipakai admin untuk menentukan tanggal distribusi, petugas, depot awal, dan daftar tujuan penerima.

## Ruang lingkup

- Tabel `distribution_schedules` untuk data jadwal utama.
- Tabel `distribution_schedule_destinations` untuk daftar tujuan per jadwal.
- Relasi jadwal ke petugas, depot lokasi, dan tujuan distribusi.
- CRUD jadwal distribusi untuk admin.
- Endpoint tambah dan hapus tujuan pada jadwal.
- Validasi minimal satu tujuan, tujuan tidak duplikat, dan depot tidak boleh menjadi tujuan.
- Total porsi dihitung otomatis dari penerima yang dipilih.

## Catatan batasan

Phase ini masih berada di tahap perencanaan jadwal. Proses eksekusi distribusi aktual, status perjalanan, bukti serah terima, dan tracking realtime masuk ke phase berikutnya.
