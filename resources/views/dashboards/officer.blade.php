<x-layouts.app>
    <h1>Dashboard Petugas Distribusi</h1>
    <p>Petugas dapat melihat rute distribusi, detail tujuan, dan memperbarui status distribusi.</p>
    <p><a href="{{ route('distribution-runs.index') }}">Kelola Distribusi Hari Ini</a></p>
    <p><a href="{{ route('route-plans.index') }}">Lihat Rute Greedy</a></p>
    <p><a href="{{ route('password.edit') }}">Ubah password</a></p>
</x-layouts.app>
