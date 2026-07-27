<x-layouts.app>
    <h1>Rute Greedy Distribusi</h1>
    <p>Daftar rute hasil algoritma Greedy nearest neighbor dari depot ke tujuan distribusi.</p>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Kode Rute</th>
                <th>Distribusi</th>
                <th>Petugas</th>
                <th>Algoritma</th>
                <th>Jarak</th>
                <th>Estimasi</th>
                <th>Aksi</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($routePlans as $routePlan)
                <tr>
                    <td>{{ $routePlan->code }}</td>
                    <td>{{ $routePlan->run->code }} - {{ $routePlan->run->schedule->scheduled_date->format('d/m/Y') }}</td>
                    <td>{{ $routePlan->run->officer->name }}</td>
                    <td>{{ str($routePlan->algorithm)->replace('_', ' ')->title() }}</td>
                    <td>{{ $routePlan->total_distance_km }} km</td>
                    <td>{{ $routePlan->total_estimated_minutes }} menit</td>
                    <td><a href="{{ route('route-plans.show', $routePlan) }}">Detail</a></td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Belum ada rute distribusi.</td>
                </tr>
            @endforelse
        </tbody>
    </table>

    {{ $routePlans->links() }}
</x-layouts.app>
