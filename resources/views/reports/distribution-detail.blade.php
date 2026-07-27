<x-layouts.app>
    <h1>Detail Laporan Distribusi</h1>
    <p>Detail akhir distribusi, tujuan, porsi, waktu, jarak, dan timeline status untuk kebutuhan laporan/demo.</p>

    <p>
        <a href="{{ route('reports.distributions.index') }}">Kembali ke laporan</a>
        |
        <a href="{{ route('distribution-runs.show', $run) }}">Buka detail distribusi</a>
    </p>

    <h2>Informasi distribusi</h2>
    <ul>
        <li>Kode distribusi: {{ $run->code }}</li>
        <li>Tanggal jadwal: {{ $run->schedule->scheduled_date->format('d/m/Y') }}</li>
        <li>Petugas: {{ $run->officer->name }}</li>
        <li>Depot: {{ $run->schedule->depot->name }}</li>
        <li>Status akhir: {{ str($run->status)->replace('_', ' ')->title() }}</li>
        <li>Mulai: {{ $run->started_at?->format('d/m/Y H:i') ?? '-' }}</li>
        <li>Selesai: {{ $run->completed_at?->format('d/m/Y H:i') ?? '-' }}</li>
        <li>Catatan: {{ $run->notes ?? '-' }}</li>
    </ul>

    <h2>Ringkasan angka</h2>
    <ul>
        <li>Total tujuan: {{ $metrics['total_destinations'] }}</li>
        <li>Tujuan terkirim: {{ $metrics['delivered_destinations'] }}</li>
        <li>Tujuan gagal/kendala: {{ $metrics['failed_destinations'] }}</li>
        <li>Total porsi rencana: {{ $metrics['planned_portions'] }}</li>
        <li>Total porsi terkirim: {{ $metrics['delivered_portions'] }}</li>
        <li>Total jarak: {{ $metrics['total_distance_km'] }} km</li>
        <li>Estimasi waktu: {{ $metrics['estimated_minutes'] ?? '-' }} menit</li>
        <li>Waktu aktual: {{ $metrics['actual_duration_minutes'] ?? '-' }} menit</li>
    </ul>

    <h2>Detail tujuan</h2>
    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Urutan</th>
                <th>Lokasi</th>
                <th>Penerima</th>
                <th>Status</th>
                <th>Porsi</th>
                <th>Tiba</th>
                <th>Selesai</th>
                <th>Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($run->destinations->sortBy('sequence_order') as $destination)
                <tr>
                    <td>{{ $destination->sequence_order }}</td>
                    <td>{{ $destination->location->name }}</td>
                    <td>{{ $destination->recipient->name }}</td>
                    <td>{{ str($destination->status)->replace('_', ' ')->title() }}</td>
                    <td>{{ $destination->delivered_portion_count ?? 0 }} / {{ $destination->planned_portion_count }}</td>
                    <td>{{ $destination->arrived_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $destination->delivered_at?->format('d/m/Y H:i') ?? '-' }}</td>
                    <td>{{ $destination->proof_notes ?? '-' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <h2>Timeline status</h2>
    <ol>
        @foreach ($statusTimeline as $item)
            <li>
                {{ $item['time'] ?? '-' }} -
                {{ $item['label'] }}
                ({{ str($item['status'])->replace('_', ' ')->title() }})
                @if ($item['notes'])
                    — {{ $item['notes'] }}
                @endif
            </li>
        @endforeach
    </ol>
</x-layouts.app>
