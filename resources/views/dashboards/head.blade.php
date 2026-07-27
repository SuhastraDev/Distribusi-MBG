<x-layouts.app>
    <h1>Dashboard Kepala SPPG</h1>
    <p>Kepala SPPG dapat memantau distribusi dan melihat laporan.</p>
    <p><a href="{{ route('distribution-runs.index') }}">Pantau Distribusi Aktual</a></p>
    <p><a href="{{ route('route-plans.index') }}">Pantau Rute Greedy</a></p>
    <p><a href="{{ route('reports.distributions.index') }}">Lihat Laporan Distribusi</a></p>
    <p><a href="{{ route('password.edit') }}">Ubah password</a></p>
</x-layouts.app>
