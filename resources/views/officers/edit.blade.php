<x-layouts.app>
    <h1>Edit Petugas Distribusi</h1>

    <form method="POST" action="{{ route('officers.update', $officer) }}">
        @method('PUT')
        @include('officers._form', ['submitLabel' => 'Update Petugas'])
    </form>
</x-layouts.app>
