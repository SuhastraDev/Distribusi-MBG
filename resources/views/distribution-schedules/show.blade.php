<x-layouts.app>
    <h1>Detail Jadwal Distribusi</h1>

    <p><strong>Kode:</strong> {{ $distributionSchedule->code }}</p>
    <p><strong>Tanggal:</strong> {{ $distributionSchedule->scheduled_date->format('d/m/Y') }}</p>
    <p><strong>Petugas:</strong> {{ $distributionSchedule->officer->name }}</p>
    <p><strong>Depot:</strong> {{ $distributionSchedule->depot->name }}</p>
    <p><strong>Total Porsi:</strong> {{ $distributionSchedule->total_portions }}</p>
    <p><strong>Status:</strong> {{ str($distributionSchedule->status)->replace('_', ' ')->title() }}</p>
    <p><strong>Catatan:</strong> {{ $distributionSchedule->notes ?: '-' }}</p>

    <p>
        <a href="{{ route('distribution-schedules.edit', $distributionSchedule) }}">Edit</a>
        <a href="{{ route('distribution-schedules.index') }}">Kembali</a>
    </p>

    @if ($distributionSchedule->status !== 'cancelled')
        <form method="POST" action="{{ route('distribution-schedules.destroy', $distributionSchedule) }}">
            @csrf
            @method('DELETE')
            <button type="submit">Batalkan Jadwal</button>
        </form>
    @endif

    <h2>Daftar Tujuan</h2>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Urutan</th>
                <th>Lokasi</th>
                <th>Penerima</th>
                <th>Porsi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributionSchedule->destinations as $destination)
                <tr>
                    <td>{{ $destination->sequence_order }}</td>
                    <td>{{ $destination->location->name }}</td>
                    <td>{{ $destination->recipient->name }}</td>
                    <td>{{ $destination->portion_count }}</td>
                    <td>
                        <form method="POST" action="{{ route('distribution-schedules.destinations.destroy', [$distributionSchedule, $destination]) }}">
                            @csrf
                            @method('DELETE')
                            <button type="submit">Hapus</button>
                        </form>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5">Belum ada tujuan distribusi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    <h2>Tambah Tujuan</h2>

    <form method="POST" action="{{ route('distribution-schedules.destinations.store', $distributionSchedule) }}">
        @csrf
        <label for="recipient_id">Penerima Aktif</label><br>
        <select id="recipient_id" name="recipient_id" required>
            <option value="">Pilih penerima</option>
            @foreach ($recipients as $recipient)
                <option value="{{ $recipient->id }}" @selected((int) old('recipient_id') === $recipient->id)>
                    {{ $recipient->name }} - {{ $recipient->location->name }} ({{ $recipient->portion_count }} porsi)
                </option>
            @endforeach
        </select>
        @error('recipient_id') <p style="color: #dc2626;">{{ $message }}</p> @enderror

        <button type="submit">Tambah Tujuan</button>
    </form>
</x-layouts.app>
