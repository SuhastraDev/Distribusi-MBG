# Backend Phase 8 - Modul Rute Greedy

Phase 8 menambahkan modul perhitungan rute distribusi menggunakan algoritma Greedy nearest neighbor.

## Ruang lingkup

- Tabel `route_plans` untuk menyimpan hasil generate rute distribusi.
- Tabel `route_plan_steps` untuk menyimpan urutan titik rute.
- Model `RoutePlan` dan `RoutePlanStep`.
- Service `GreedyRouteService` untuk menghitung urutan tujuan.
- Endpoint generate rute dari distribusi aktual.
- Endpoint list dan detail rute.
- Validasi rute hanya bisa dibuat jika distribusi memiliki tujuan dan semua lokasi memiliki koordinat.
- Hitung jarak antar lokasi memakai formula Haversine.
- Hitung estimasi waktu berbasis kecepatan rata-rata 25 km/jam.
- Seeder demo otomatis membuat rute dari `RUN-DEMO-001`.

## Cara kerja Greedy

1. Mulai dari depot jadwal distribusi.
2. Cari tujuan terdekat dari posisi saat ini.
3. Masukkan tujuan tersebut ke urutan rute.
4. Ulangi sampai semua tujuan masuk ke rute.
5. Simpan jarak per langkah dan jarak kumulatif.

## Catatan batasan

Rute Phase 8 memakai jarak garis lurus Haversine, bukan jarak jalan aktual. Integrasi OSRM/GraphHopper atau routing jalan bisa ditambahkan pada phase lanjutan jika dibutuhkan.
