<x-layouts.app>
    <h1>Tambah Jadwal Distribusi</h1>

    <form method="POST" action="{{ route('distribution-schedules.store') }}">
        @include('distribution-schedules._form', ['submitLabel' => 'Simpan Jadwal'])
    </form>

    <p><a href="{{ route('distribution-schedules.index') }}">Kembali ke daftar jadwal</a></p>
</x-layouts.app>
