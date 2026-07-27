<x-layouts.app>
    <h1>Data Petugas Distribusi</h1>
    <p>Kelola akun dan profil petugas distribusi MBG.</p>

    <p>
        <a href="{{ route('officers.create') }}">Tambah Petugas</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Nama</th>
                <th>Email</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($officers as $officer)
                <tr>
                    <td>{{ $officer->officer_code }}</td>
                    <td>{{ $officer->name }}</td>
                    <td>{{ $officer->user->email }}</td>
                    <td>{{ $officer->status === 'active' ? 'Aktif' : 'Nonaktif' }}</td>
                    <td>
                        <a href="{{ route('officers.show', $officer) }}">Detail</a>
                        <a href="{{ route('officers.edit', $officer) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada data petugas.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $officers->links() }}
</x-layouts.app>
