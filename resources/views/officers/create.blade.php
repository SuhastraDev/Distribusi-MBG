<x-layouts.app>
    <h1>Tambah Petugas Distribusi</h1>

    <form method="POST" action="{{ route('officers.store') }}">
        @include('officers._form', ['submitLabel' => 'Simpan Petugas'])
    </form>
</x-layouts.app>
