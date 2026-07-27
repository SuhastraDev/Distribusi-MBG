<x-layouts.app>
    <h1>Data Lokasi Distribusi</h1>
    <p>Kelola depot dan titik tujuan distribusi MBG.</p>

    <p>
        <a href="{{ route('locations.create') }}">Tambah Lokasi</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Tipe</th>
                <th>Koordinat</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($locations as $location)
                <tr>
                    <td>{{ $location->code }}</td>
                    <td>{{ $location->name }}</td>
                    <td>{{ $location->typeLabel() }}</td>
                    <td>{{ $location->latitude }}, {{ $location->longitude }}</td>
                    <td>{{ $location->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <a href="{{ route('locations.show', $location) }}">Detail</a>
                        <a href="{{ route('locations.edit', $location) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada data lokasi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $locations->links() }}
</x-layouts.app>
