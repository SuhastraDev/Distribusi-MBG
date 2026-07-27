<x-layouts.app>
    <h1>Laporan Distribusi</h1>
    <p>Ringkasan distribusi MBG berdasarkan status, porsi, tujuan, dan jarak rute.</p>

    <form method="GET" action="{{ route('reports.distributions.index') }}">
        <label for="date_from">Dari tanggal</label><br>
        <input id="date_from" name="date_from" type="date" value="{{ $filters['date_from'] ?? '' }}">

        <br>

        <label for="date_to">Sampai tanggal</label><br>
        <input id="date_to" name="date_to" type="date" value="{{ $filters['date_to'] ?? '' }}">

        <br>

        <label for="officer_id">Petugas</label><br>
        <select id="officer_id" name="officer_id">
            <option value="">Semua petugas</option>
            @foreach ($officers as $officer)
                <option value="{{ $officer->id }}" @selected((string) ($filters['officer_id'] ?? '') === (string) $officer->id)>
                    {{ $officer->name }}
                </option>
            @endforeach
        </select>

        <br>

        <label for="status">Status</label><br>
        <select id="status" name="status">
            <option value="">Semua status</option>
            @foreach (['ready', 'in_progress', 'completed', 'cancelled'] as $status)
                <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                    {{ str($status)->replace('_', ' ')->title() }}
                </option>
            @endforeach
        </select>

        <br>
        <button type="submit">Filter</button>
        <a href="{{ route('reports.distributions.index') }}">Reset</a>
    </form>

    <p>
        <a href="{{ route('reports.distributions.export', request()->query()) }}">Export CSV</a>
        |
        <a href="{{ route('reports.distributions.export-excel', request()->query()) }}">Export Excel</a>
    </p>

    <h2>Ringkasan</h2>

    <ul>
        <li>Total distribusi: {{ $summary['total_runs'] }}</li>
        <li>Ready: {{ $summary['ready_runs'] }}</li>
        <li>Berjalan: {{ $summary['in_progress_runs'] }}</li>
        <li>Selesai: {{ $summary['completed_runs'] }}</li>
        <li>Dibatalkan: {{ $summary['cancelled_runs'] }}</li>
        <li>Total tujuan: {{ $summary['total_destinations'] }}</li>
        <li>Tujuan terkirim: {{ $summary['delivered_destinations'] }}</li>
        <li>Porsi rencana: {{ $summary['planned_portions'] }}</li>
        <li>Porsi terkirim: {{ $summary['delivered_portions'] }}</li>
        <li>Total jarak rute: {{ $summary['total_distance_km'] }} km</li>
    </ul>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode</th>
                <th>Tanggal</th>
                <th>Petugas</th>
                <th>Depot</th>
                <th>Status</th>
                <th>Tujuan</th>
                <th>Porsi</th>
                <th>Jarak</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($distributionRuns as $run)
                <tr>
                    <td>{{ $run->code }}</td>
                    <td>{{ $run->schedule->scheduled_date->format('d/m/Y') }}</td>
                    <td>{{ $run->officer->name }}</td>
                    <td>{{ $run->schedule->depot->name }}</td>
                    <td>{{ str($run->status)->replace('_', ' ')->title() }}</td>
                    <td>{{ $run->destinations->where('status', 'delivered')->count() }} / {{ $run->destinations->count() }}</td>
                    <td>{{ $run->destinations->where('status', 'delivered')->sum('delivered_portion_count') }} / {{ $run->destinations->sum('planned_portion_count') }}</td>
                    <td>{{ $run->routePlan?->total_distance_km ?? 0 }} km</td>
                    <td>
                        <a href="{{ route('reports.distributions.show', $run) }}">Detail Laporan</a>
                        |
                        <a href="{{ route('distribution-runs.show', $run) }}">Detail Distribusi</a>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="9">Belum ada data distribusi sesuai filter.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $distributionRuns->links() }}
</x-layouts.app>
