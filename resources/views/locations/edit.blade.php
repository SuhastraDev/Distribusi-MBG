<x-layouts.app>
    <h1>Edit Lokasi Distribusi</h1>

    <form method="POST" action="{{ route('locations.update', $location) }}">
        @method('PUT')
        @include('locations._form', ['submitLabel' => 'Update Lokasi'])
    </form>
</x-layouts.app>
