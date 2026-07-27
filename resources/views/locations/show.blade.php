<x-layouts.app>
    <h1>Detail Lokasi Distribusi</h1>

    <dl>
        <dt>Kode</dt>
        <dd>{{ $location->code }}</dd>

        <dt>Nama</dt>
        <dd>{{ $location->name }}</dd>

        <dt>Tipe</dt>
        <dd>{{ $location->typeLabel() }}</dd>

        <dt>Alamat</dt>
        <dd>{{ $location->address ?? '-' }}</dd>

        <dt>Latitude</dt>
        <dd>{{ $location->latitude }}</dd>

        <dt>Longitude</dt>
        <dd>{{ $location->longitude }}</dd>

        <dt>Status</dt>
        <dd>{{ $location->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd>
    </dl>

    <p>
        <a href="{{ route('locations.index') }}">Kembali</a>
        <a href="{{ route('locations.edit', $location) }}">Edit</a>
    </p>

    @if ($location->status === 'active')
        <form method="POST" action="{{ route('locations.destroy', $location) }}">
            @csrf
            @method('DELETE')
            <button type="submit">Nonaktifkan Lokasi</button>
        </form>
    @endif
</x-layouts.app>
