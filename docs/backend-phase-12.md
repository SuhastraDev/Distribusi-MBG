# Backend Phase 12 - API Pendukung Frontend

Phase 12 menambahkan endpoint JSON internal untuk kebutuhan frontend/dashboard.

## Endpoint

- `GET /api/frontend/dashboard-summary`
- `GET /api/frontend/distribution-runs`
- `GET /api/frontend/distribution-runs/{distribution_run}`
- `GET /api/frontend/route-plans/{route_plan}/map`
- `GET /api/frontend/reports/distributions/summary`

## Ruang lingkup

- Summary dashboard distribusi dan rute.
- List distribusi aktual dengan pagination JSON.
- Detail distribusi aktual beserta tujuan distribusi.
- Data map rute untuk frontend.
- Summary laporan distribusi dengan filter status dan tanggal.

## Akses

- Admin, petugas, dan kepala SPPG dapat membaca data dashboard/distribusi/rute.
- Summary laporan hanya untuk admin dan kepala SPPG.
- Endpoint memakai auth session Laravel karena frontend masih berada dalam aplikasi yang sama.
