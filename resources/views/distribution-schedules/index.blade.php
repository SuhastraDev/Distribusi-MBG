<x-layouts.app>
    <h1>Jadwal Distribusi</h1>
    <p>Kelola jadwal rencana distribusi MBG dari depot ke daftar tujuan penerima.</p>

    <p>
        <a href="{{ route('distribution-schedules.create') }}">Tambah Jadwal</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Petugas</th>
                <th>Depot</th>
                <th>Total Porsi</th>
                <th>Status</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributionSchedules as $schedule)
                <tr>
                    <td>{{ $schedule->code }}</td>
                    <td>{{ $schedule->scheduled_date->format('d/m/Y') }}</td>
                    <td>{{ $schedule->officer->name }}</td>
                    <td>{{ $schedule->depot->name }}</td>
                    <td>{{ $schedule->total_portions }}</td>
                    <td>{{ str($schedule->status)->replace('_', ' ')->title() }}</td>
                    <td>
                        <a href="{{ route('distribution-schedules.show', $schedule) }}">Detail</a>
                        <a href="{{ route('distribution-schedules.edit', $schedule) }}">Edit</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada jadwal distribusi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $distributionSchedules->links() }}
</x-layouts.app>
