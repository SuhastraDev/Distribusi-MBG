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
    <p><strong>Data peta:</strong> <a href="{{ route('route-plans.map-data', $routePlan) }}">JSON Map Data</a></p>

    <p>
        <a href="{{ route('distribution-runs.show', $routePlan->run) }}">Kembali ke distribusi</a>
        <a href="{{ route('route-plans.index') }}">Daftar rute</a>
    </p>

    @php
        $routeMapData = [
            'center' => [
                'latitude' => (float) $routePlan->run->schedule->depot->latitude,
                'longitude' => (float) $routePlan->run->schedule->depot->longitude,
            ],
            'steps' => $routePlan->steps->map(fn ($step): array => [
                'order' => $step->step_order,
                'type' => $step->step_type,
                'name' => $step->location->name,
                'address' => $step->location->address,
                'latitude' => (float) $step->location->latitude,
                'longitude' => (float) $step->location->longitude,
                'recipient' => $step->runDestination?->recipient?->name,
                'status' => $step->runDestination?->status,
                'distance' => (float) $step->distance_from_previous_km,
            ])->values(),
        ];
    @endphp

    <link
        rel="stylesheet"
        href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css"
        integrity="sha256-p4NxAoJBhIINfQPDQxOCLdYARl3QXvhehF7v8s8dUdk="
        crossorigin=""
    >

    <div id="route-map" style="height: 420px; border: 1px solid #cbd5e1; margin: 24px 0;"></div>

    <script
        src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"
        integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo="
        crossorigin=""
    ></script>
    <script>
        const routeMapData = @json($routeMapData);

        const routeMap = L.map('route-map').setView([
            routeMapData.center.latitude,
            routeMapData.center.longitude,
        ], 12);

        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors',
        }).addTo(routeMap);

        const latLngs = routeMapData.steps.map((step) => [step.latitude, step.longitude]);

        routeMapData.steps.forEach((step) => {
            const label = step.type === 'start'
                ? `Depot: ${step.name}`
                : `${step.order - 1}. ${step.name}`;
            const popup = `
                <strong>${label}</strong><br>
                ${step.recipient ? `Penerima: ${step.recipient}<br>` : ''}
                ${step.status ? `Status: ${step.status}<br>` : ''}
                Jarak dari titik sebelumnya: ${step.distance} km<br>
                ${step.address ?? ''}
            `;

            L.marker([step.latitude, step.longitude])
                .addTo(routeMap)
                .bindPopup(popup);
        });

        if (latLngs.length > 1) {
            L.polyline(latLngs, {color: '#2563eb', weight: 4}).addTo(routeMap);
            routeMap.fitBounds(latLngs, {padding: [32, 32]});
        }
    </script>

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
