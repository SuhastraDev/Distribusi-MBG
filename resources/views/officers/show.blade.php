<x-layouts.app>
    <h1>Detail Petugas Distribusi</h1>

    <dl>
        <dt>Kode</dt>
        <dd>{{ $officer->officer_code }}</dd>

        <dt>Nama</dt>
        <dd>{{ $officer->name }}</dd>

        <dt>Email Login</dt>
        <dd>{{ $officer->user->email }}</dd>

        <dt>Nomor HP</dt>
        <dd>{{ $officer->phone ?? '-' }}</dd>

        <dt>Alamat</dt>
        <dd>{{ $officer->address ?? '-' }}</dd>

        <dt>Status</dt>
        <dd>{{ $officer->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd>
    </dl>

    <p>
        <a href="{{ route('officers.index') }}">Kembali</a>
        <a href="{{ route('officers.edit', $officer) }}">Edit</a>
    </p>

    @if ($officer->status === 'active')
        <form method="POST" action="{{ route('officers.destroy', $officer) }}">
            @csrf
            @method('DELETE')
            <button type="submit">Nonaktifkan Petugas</button>
        </form>
    @endif
</x-layouts.app>
