# Backend Phase 10 - Monitoring Posisi Petugas

Phase 10 menambahkan fondasi monitoring posisi petugas untuk distribusi yang sedang berjalan.

## Ruang lingkup

- Tabel `officer_positions` untuk menyimpan riwayat posisi petugas per distribusi.
- Model `OfficerPosition`.
- Relasi distribusi aktual ke riwayat posisi dan posisi terbaru.
- Endpoint update posisi petugas.
- Endpoint JSON posisi terbaru petugas.
- Data posisi terbaru masuk ke endpoint JSON peta rute.
- Marker posisi petugas tampil di halaman detail rute.
- Form sederhana untuk simulasi update posisi dari halaman distribusi aktual.

## Validasi

- Latitude wajib di rentang -90 sampai 90.
- Longitude wajib di rentang -180 sampai 180.
- Akurasi GPS opsional dan tidak boleh negatif.
- Posisi hanya bisa diperbarui saat distribusi berstatus `in_progress`.
- Petugas hanya bisa update posisi distribusi miliknya.

## Catatan batasan

Phase ini belum memakai WebSocket atau browser geolocation otomatis. Update posisi masih berupa endpoint/form manual sebagai fondasi backend untuk realtime tracking pada phase lanjutan.
