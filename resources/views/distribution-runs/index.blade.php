<x-layouts.app>
    <h1>Distribusi Aktual</h1>
    <p>Pantau dan eksekusi distribusi berdasarkan jadwal yang sudah dibuat.</p>

    @if (in_array(auth()->user()->role->name, ['admin', 'petugas'], true))
        <p><a href="{{ route('distribution-runs.create') }}">Buat Distribusi dari Jadwal</a></p>
    @endif

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Jadwal</th>
                <th>Petugas</th>
                <th>Status</th>
                <th>Mulai</th>
                <th>Selesai</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributionRuns as $run)
                <tr>
                    <td>{{ $run->code }}</td>
                    <td>{{ $run->schedule->code }} - {{ $run->schedule->scheduled_date->format('d/m/Y') }}</td>
                    <td>{{ $run->officer->name }}</td>
                    <td>{{ str($run->status)->replace('_', ' ')->title() }}</td>
                    <td>{{ $run->started_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $run->completed_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td><a href="{{ route('distribution-runs.show', $run) }}">Detail</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada distribusi aktual.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $distributionRuns->links() }}
</x-layouts.app>
