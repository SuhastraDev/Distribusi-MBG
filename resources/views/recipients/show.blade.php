<x-layouts.app>
    <h1>Detail Penerima MBG</h1>

    <dl>
        <dt>Kode</dt>
        <dd>{{ $recipient->code }}</dd>

        <dt>Nama</dt>
        <dd>{{ $recipient->name }}</dd>

        <dt>Lokasi</dt>
        <dd>{{ $recipient->location->name }}</dd>

        <dt>Jumlah Porsi</dt>
        <dd>{{ $recipient->portion_count }}</dd>

        <dt>Catatan</dt>
        <dd>{{ $recipient->notes ?? '-' }}</dd>

        <dt>Status</dt>
        <dd>{{ $recipient->status === 'active' ? 'Aktif' : 'Nonaktif' }}</dd>
    </dl>

    <p>
        <a href="{{ route('recipients.index') }}">Kembali</a>
        <a href="{{ route('recipients.edit', $recipient) }}">Edit</a>
    </p>

    @if ($recipient->status === 'active')
        <form method="POST" action="{{ route('recipients.destroy', $recipient) }}">
            @csrf
            @method('DELETE')
            <button type="submit">Nonaktifkan Penerima</button>
        </form>
    @endif
</x-layouts.app>
