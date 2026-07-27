<x-layouts.app>
    <h1>Data Penerima MBG</h1>
    <p>Kelola penerima atau kelompok penerima MBG per lokasi distribusi.</p>

    <p>
        <a href="{{ route('recipients.create') }}">Tambah Penerima</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Lokasi</th>
                <th>Porsi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($recipients as $recipient)
                <tr>
                    <td>{{ $recipient->code }}</td>
                    <td>{{ $recipient->name }}</td>
                    <td>{{ $recipient->location->name }}</td>
                    <td>{{ $recipient->portion_count }}</td>
                    <td>{{ $recipient->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <a href="{{ route('recipients.show', $recipient) }}">Detail</a>
                        <a href="{{ route('recipients.edit', $recipient) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6">Belum ada data penerima MBG.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $recipients->links() }}
</x-layouts.app>
