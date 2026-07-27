# Backend Phase 9 - Peta Rute dan Monitoring Visual

Phase 9 menambahkan tampilan peta rute distribusi menggunakan Leaflet dan OpenStreetMap.

## Ruang lingkup

- Halaman detail rute menampilkan peta Leaflet.
- Marker depot dan tujuan distribusi ditampilkan berdasarkan `route_plan_steps`.
- Polyline rute ditarik mengikuti urutan hasil algoritma Greedy.
- Popup marker menampilkan lokasi, penerima, status tujuan, jarak dari titik sebelumnya, dan alamat.
- Endpoint JSON `route-plans.map-data` dibuat untuk kebutuhan frontend/peta.
- Akses peta bisa dilihat oleh admin, petugas, dan kepala SPPG.

## Teknologi peta

- Leaflet JS untuk render peta.
- OpenStreetMap tile layer sebagai map provider gratis.
- Data koordinat berasal dari tabel `locations`.

## Catatan batasan

Peta Phase 9 menampilkan rute hasil perhitungan Greedy sebagai garis antar titik. Ini belum memakai routing jalan aktual dan belum tracking GPS realtime petugas.
