<x-layouts.app>
    <h1>Detail Rute Greedy</h1>

    <p><strong>Kode Rute:</strong> {{ $routePlan->code }}</p>
    <p><strong>Distribusi:</strong> {{ $routePlan->run->code }}</p>
    <p><strong>Jadwal:</strong> {{ $routePlan->run->schedule->code }} - {{ $routePlan->run->schedule->scheduled_date->format('d/m/Y') }}</p>
    <p><strong>Depot:</strong> {{ $routePlan->run->schedule->depot->name }}</p>
    <p><strong>Petugas:</strong> {{ $routePlan->run->officer->name }}</p>
    <p><strong>Algoritma:</strong> {{ str($routePlan->algorithm)->replace('_', ' ')->title() }}</p>
    <p><strong>Total jarak:</strong> {{ $routePlan->total_distance_km }} km</p>
    <p><strong>Estimasi waktu:</strong> {{ $routePlan->total_estimated_minutes }} menit</p>

    <p>
        <a href="{{ route('distribution-runs.show', $routePlan->run) }}">Kembali ke distribusi</a>
        <a href="{{ route('route-plans.index') }}">Daftar rute</a>
    </p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Urutan</th>
                <th>Tipe</th>
                <th>Lokasi</th>
                <th>Penerima</th>
                <th>Koordinat</th>
                <th>Jarak dari titik sebelumnya</th>
                <th>Jarak kumulatif</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($routePlan->steps as $step)
                <tr>
                    <td>{{ $step->step_order }}</td>
                    <td>{{ str($step->step_type)->replace('_', ' ')->title() }}</td>
                    <td>{{ $step->location->name }}</td>
                    <td>{{ $step->runDestination?->recipient?->name ?? '-' }}</td>
                    <td>{{ $step->location->latitude }}, {{ $step->location->longitude }}</td>
                    <td>{{ $step->distance_from_previous_km }} km</td>
                    <td>{{ $step->cumulative_distance_km }} km</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-layouts.app>
