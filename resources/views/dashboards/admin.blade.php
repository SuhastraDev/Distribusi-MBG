<x-layouts.app>
    <h1>Dashboard Admin</h1>
    <p>Admin dapat mengelola data master, jadwal distribusi, generate rute, monitoring, dan laporan.</p>
    <p><a href="{{ route('officers.index') }}">Kelola Petugas Distribusi</a></p>
    <p><a href="{{ route('locations.index') }}">Kelola Lokasi Distribusi</a></p>
    <p><a href="{{ route('recipients.index') }}">Kelola Penerima MBG</a></p>
    <p><a href="{{ route('distribution-schedules.index') }}">Kelola Jadwal Distribusi</a></p>
    <p><a href="{{ route('distribution-runs.index') }}">Monitoring Distribusi Aktual</a></p>
    <p><a href="{{ route('password.edit') }}">Ubah password</a></p>
</x-layouts.app>
