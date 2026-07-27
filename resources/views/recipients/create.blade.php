<x-layouts.app>
    <h1>Tambah Penerima MBG</h1>

    <form method="POST" action="{{ route('recipients.store') }}">
        @include('recipients._form', ['submitLabel' => 'Simpan Penerima'])
    </form>
</x-layouts.app>
