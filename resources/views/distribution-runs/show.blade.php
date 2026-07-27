<x-layouts.app>
    <h1>Detail Distribusi Aktual</h1>

    <p><strong>Kode:</strong> {{ $distributionRun->code }}</p>
    <p><strong>Jadwal:</strong> {{ $distributionRun->schedule->code }} - {{ $distributionRun->schedule->scheduled_date->format('d/m/Y') }}</p>
    <p><strong>Depot:</strong> {{ $distributionRun->schedule->depot->name }}</p>
    <p><strong>Petugas:</strong> {{ $distributionRun->officer->name }}</p>
    <p><strong>Status:</strong> {{ str($distributionRun->status)->replace('_', ' ')->title() }}</p>
    <p><strong>Mulai:</strong> {{ $distributionRun->started_at?->format('d/m/Y H:i') ?? '-' }}</p>
    <p><strong>Selesai:</strong> {{ $distributionRun->completed_at?->format('d/m/Y H:i') ?? '-' }}</p>
    <p><strong>Catatan:</strong> {{ $distributionRun->notes ?: '-' }}</p>
    <p><strong>Total porsi terkirim:</strong> {{ $distributionRun->deliveredPortions() }}</p>
    <p><strong>Posisi terbaru:</strong> <a href="{{ route('distribution-runs.positions.latest', $distributionRun) }}">JSON Posisi Petugas</a></p>

    <p><a href="{{ route('distribution-runs.index') }}">Kembali</a></p>

    @if ($distributionRun->routePlan)
        <p><a href="{{ route('route-plans.show', $distributionRun->routePlan) }}">Lihat Rute Greedy</a></p>
    @endif

    @if (in_array(auth()->user()->role->name, ['admin', 'petugas'], true))
        <form method="POST" action="{{ route('distribution-runs.route-plan.generate', $distributionRun) }}">
            @csrf
            <button type="submit">{{ $distributionRun->routePlan ? 'Generate Ulang Rute Greedy' : 'Generate Rute Greedy' }}</button>
        </form>

        @if ($distributionRun->status === 'ready')
            <form method="POST" action="{{ route('distribution-runs.start', $distributionRun) }}">
                @csrf
                <button type="submit">Mulai Distribusi</button>
            </form>
        @endif

        @if ($distributionRun->status === 'in_progress')
            <h2>Update Posisi Petugas</h2>

            <form method="POST" action="{{ route('distribution-runs.positions.store', $distributionRun) }}">
                @csrf

                <label for="latitude">Latitude</label><br>
                <input id="latitude" name="latitude" type="number" step="0.0000001" value="{{ old('latitude') }}" required>
                @error('latitude') <p style="color: #dc2626;">{{ $message }}</p> @enderror

                <br>

                <label for="longitude">Longitude</label><br>
                <input id="longitude" name="longitude" type="number" step="0.0000001" value="{{ old('longitude') }}" required>
                @error('longitude') <p style="color: #dc2626;">{{ $message }}</p> @enderror

                <br>

                <label for="accuracy_meters">Akurasi GPS (meter)</label><br>
                <input id="accuracy_meters" name="accuracy_meters" type="number" step="0.01" min="0" value="{{ old('accuracy_meters') }}">
                @error('accuracy_meters') <p style="color: #dc2626;">{{ $message }}</p> @enderror

                <br>

                <button type="submit">Update Posisi</button>
            </form>

            <form method="POST" action="{{ route('distribution-runs.complete', $distributionRun) }}">
                @csrf
                <button type="submit">Selesaikan Distribusi</button>
            </form>
        @endif

        @if (! in_array($distributionRun->status, ['completed', 'cancelled'], true))
            <form method="POST" action="{{ route('distribution-runs.cancel', $distributionRun) }}">
                @csrf
                <button type="submit">Batalkan Distribusi</button>
            </form>
        @endif
    @endif

    <h2>Status Tujuan</h2>

    <table border="1" cellpadding="8" cellspacing="0" width="100%">
        <thead>
            <tr>
                <th>Urutan</th>
                <th>Lokasi</th>
                <th>Penerima</th>
                <th>Rencana Porsi</th>
                <th>Terkirim</th>
                <th>Status</th>
                <th>Waktu</th>
                <th>Update</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($distributionRun->destinations->sortBy('sequence_order') as $destination)
                <tr>
                    <td>{{ $destination->sequence_order }}</td>
                    <td>{{ $destination->location->name }}</td>
                    <td>{{ $destination->recipient->name }}</td>
                    <td>{{ $destination->planned_portion_count }}</td>
                    <td>{{ $destination->delivered_portion_count ?? '-' }}</td>
                    <td>{{ str($destination->status)->replace('_', ' ')->title() }}</td>
                    <td>
                        Tiba: {{ $destination->arrived_at?->format('H:i') ?? '-' }}<br>
                        Terkirim: {{ $destination->delivered_at?->format('H:i') ?? '-' }}
                    </td>
                    <td>
                        @if ($distributionRun->status === 'in_progress' && in_array(auth()->user()->role->name, ['admin', 'petugas'], true))
                            <form method="POST" action="{{ route('distribution-runs.destinations.update', [$distributionRun, $destination]) }}">
                                @csrf
                                @method('PUT')

                                <select name="status" required>
                                    <option value="arrived" @selected(old('status', $destination->status) === 'arrived')>Tiba</option>
                                    <option value="delivered" @selected(old('status', $destination->status) === 'delivered')>Terkirim</option>
                                    <option value="skipped" @selected(old('status', $destination->status) === 'skipped')>Lewati</option>
                                </select>

                                <input
                                    name="delivered_portion_count"
                                    type="number"
                                    min="0"
                                    max="{{ $destination->planned_portion_count }}"
                                    value="{{ old('delivered_portion_count', $destination->delivered_portion_count ?? $destination->planned_portion_count) }}"
                                    placeholder="Porsi terkirim"
                                >

                                <input
                                    name="proof_notes"
                                    value="{{ old('proof_notes', $destination->proof_notes ?? '') }}"
                                    placeholder="Catatan bukti"
                                >

                                <button type="submit">Update</button>
                            </form>
                        @else
                            {{ $destination->proof_notes ?: '-' }}
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</x-layouts.app>
