<x-layouts.app>
    <h1>Tambah Lokasi Distribusi</h1>

    <form method="POST" action="{{ route('locations.store') }}">
        @include('locations._form', ['submitLabel' => 'Simpan Lokasi'])
    </form>
</x-layouts.app>
