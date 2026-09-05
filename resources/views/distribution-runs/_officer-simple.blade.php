@php
    $sortedDestinations = $distributionRun->destinations->sortBy('sequence_order')->values();
    $totalDestinations = $sortedDestinations->count();
    $currentDestination = $sortedDestinations->first(fn ($d) => ! in_array($d->status, ['delivered', 'skipped'], true));
    $doneCount = $sortedDestinations->filter(fn ($d) => in_array($d->status, ['delivered', 'skipped'], true))->count();
    $allDone = $distributionRun->status === 'in_progress' && $currentDestination === null && $totalDestinations > 0;

    $markers = [];
    $polyline = [];
    if ($distributionRun->routePlan) {
        foreach ($distributionRun->routePlan->steps as $step) {
            $markers[] = [
                'lat' => (float) $step->location->latitude,
                'lng' => (float) $step->location->longitude,
                'title' => $step->location->name,
                'popup' => '<strong>'.($step->step_type === 'start' ? 'Depot: ' : $step->step_order.'. ').$step->location->name.'</strong><br>'.($step->runDestination?->recipient?->name ?: '').'<br>Jarak: '.$step->distance_from_previous_km.' km',
                'type' => $step->step_type === 'start' ? 'depot' : 'destination',
                'order' => $step->step_order,
            ];
            $polyline[] = [(float) $step->location->latitude, (float) $step->location->longitude];
        }
    } else {
        if ($distributionRun->schedule->depot->latitude && $distributionRun->schedule->depot->longitude) {
            $markers[] = [
                'lat' => (float) $distributionRun->schedule->depot->latitude,
                'lng' => (float) $distributionRun->schedule->depot->longitude,
                'title' => 'Depot: '.$distributionRun->schedule->depot->name,
                'popup' => '<strong>Depot Asal: '.$distributionRun->schedule->depot->name.'</strong>',
                'type' => 'depot',
            ];
        }
        foreach ($distributionRun->destinations as $idx => $dest) {
            if ($dest->location->latitude && $dest->location->longitude) {
                $markers[] = [
                    'lat' => (float) $dest->location->latitude,
                    'lng' => (float) $dest->location->longitude,
                    'title' => $dest->location->name,
                    'popup' => '<strong>'.($idx + 1).'. '.$dest->location->name.'</strong><br>Penerima: '.$dest->recipient->name,
                    'type' => 'destination',
                    'order' => $idx + 1,
                ];
            }
        }
    }

    $officerMarker = $distributionRun->latestOfficerPosition ? [
        'lat' => (float) $distributionRun->latestOfficerPosition->latitude,
        'lng' => (float) $distributionRun->latestOfficerPosition->longitude,
        'accuracy' => (float) ($distributionRun->latestOfficerPosition->accuracy_meters ?? 0),
        'popup' => '<strong>Posisi Anda</strong><br>Waktu: '.$distributionRun->latestOfficerPosition->recorded_at->format('d/m/Y H:i:s'),
    ] : null;
@endphp

<div class="max-w-2xl mx-auto space-y-5"
     x-data="{
        tracking: false,
        watchId: null,
        gpsError: null,
        storeUrl: '{{ $distributionRun->status === 'in_progress' ? route('distribution-runs.positions.store', $distributionRun) : '' }}',
        init() {
            @if ($distributionRun->status === 'in_progress')
                this.startGps();
            @endif
            @if ($allDone)
                this.$refs.autoComplete.requestSubmit();
            @endif
        },
        startGps() {
            if (!navigator.geolocation) {
                this.gpsError = 'Perangkat tidak mendukung GPS.';
                return;
            }
            this.tracking = true;
            this.gpsError = null;
            this.watchId = navigator.geolocation.watchPosition(
                pos => this.send(pos),
                err => { this.gpsError = 'GPS terputus: ' + err.message; },
                { enableHighAccuracy: true, maximumAge: 8000, timeout: 15000 }
            );
        },
        async send(pos) {
            try {
                const res = await fetch(this.storeUrl, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name=csrf-token]').content,
                    },
                    body: JSON.stringify({
                        latitude: pos.coords.latitude,
                        longitude: pos.coords.longitude,
                        accuracy_meters: pos.coords.accuracy,
                    }),
                });
                this.gpsError = res.ok ? null : 'Gagal mengirim posisi ke server.';
            } catch (e) {
                this.gpsError = 'Koneksi terputus saat mengirim posisi GPS.';
            }
        }
     }">

    <!-- Header -->
    <div class="text-center space-y-1">
        <h2 class="text-lg font-black text-slate-800 font-mono">{{ $distributionRun->code }}</h2>
        <p class="text-xs text-slate-500">
            Depot: {{ $distributionRun->schedule->depot->name }} &bull; {{ $distributionRun->schedule->scheduled_date->format('d M Y') }}
        </p>
        @if ($distributionRun->status === 'in_progress')
            <div class="inline-flex items-center gap-1.5 text-xs font-bold" :class="gpsError ? 'text-red-600' : 'text-emerald-600'">
                <span class="relative flex h-2 w-2">
                    <span x-show="!gpsError" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2" :class="gpsError ? 'bg-red-500' : 'bg-emerald-500'"></span>
                </span>
                <span x-text="gpsError || 'GPS Live Aktif Otomatis'"></span>
            </div>
        @endif
    </div>

    @if (! empty($markers))
        <x-map :markers="$markers" :polyline="$polyline" :officer="$officerMarker" height="280px"
            :live-position-url="$distributionRun->status === 'in_progress' ? route('distribution-runs.positions.latest', $distributionRun) : null" />
    @endif

    {{-- STEP 1: belum ada rute --}}
    @if (! $distributionRun->routePlan)
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center space-y-4">
            <div class="w-14 h-14 mx-auto rounded-2xl bg-indigo-50 text-indigo-600 flex items-center justify-center">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path></svg>
            </div>
            <p class="text-sm text-slate-600">Rute pengiriman belum dibuat. Buat rute tercepat sebelum berangkat.</p>
            <form method="POST" action="{{ route('distribution-runs.route-plan.generate', $distributionRun) }}">
                @csrf
                <button type="submit" class="w-full py-3 rounded-xl bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm shadow-sm cursor-pointer">
                    Generate Rute
                </button>
            </form>
        </div>

    {{-- STEP 2: rute siap, belum berangkat --}}
    @elseif ($distributionRun->status === 'ready')
        <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center space-y-4">
            <div class="text-sm text-slate-600 space-y-1">
                <p><span class="font-bold text-slate-800">{{ $totalDestinations }}</span> tujuan &bull; <span class="font-bold text-slate-800">{{ number_format($distributionRun->routePlan->total_distance_km, 1) }} km</span> &bull; est. <span class="font-bold text-slate-800">{{ $distributionRun->routePlan->total_estimated_minutes }} menit</span></p>
            </div>
            <form method="POST" action="{{ route('distribution-runs.start', $distributionRun) }}">
                @csrf
                <button type="submit"
                        onclick="return confirm('Mulai perjalanan sekarang? GPS live akan aktif otomatis.');"
                        class="w-full py-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-black text-base shadow-sm cursor-pointer">
                    Mulai Perjalanan
                </button>
            </form>
        </div>

    {{-- STEP 3: sedang berjalan --}}
    @elseif ($distributionRun->status === 'in_progress')

        <div class="text-center text-xs font-semibold text-slate-500">
            Progres: {{ $doneCount }} / {{ $totalDestinations }} tujuan selesai
        </div>
        <div class="w-full h-2 rounded-full bg-slate-100 overflow-hidden">
            <div class="h-full bg-emerald-500" style="width: {{ $totalDestinations > 0 ? round($doneCount / $totalDestinations * 100) : 0 }}%"></div>
        </div>

        @if ($currentDestination)
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm space-y-4">
                <div class="text-center">
                    <span class="inline-block text-[11px] font-bold text-indigo-600 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">
                        Tujuan #{{ $currentDestination->sequence_order }} dari {{ $totalDestinations }}
                    </span>
                </div>
                <div class="text-center space-y-1">
                    <h3 class="text-xl font-black text-slate-800">{{ $currentDestination->location->name ?? '-' }}</h3>
                    <p class="text-sm text-slate-500">{{ $currentDestination->recipient->name ?? 'Penerima Terhapus' }}</p>
                    <p class="text-xs text-slate-400">Rencana kirim: <span class="font-bold text-slate-600">{{ number_format($currentDestination->planned_portion_count) }} porsi</span></p>
                </div>

                <form method="POST" action="{{ route('distribution-runs.destinations.update', [$distributionRun, $currentDestination]) }}">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="delivered">
                    <input type="hidden" name="delivered_portion_count" value="{{ $currentDestination->planned_portion_count }}">
                    <button type="submit"
                            onclick="return confirm('Konfirmasi paket sudah diserahkan di {{ addslashes($currentDestination->location->name ?? '') }}?');"
                            class="w-full py-4 rounded-xl bg-blue-600 hover:bg-blue-700 text-white font-black text-base shadow-sm cursor-pointer">
                        Lanjutkan (Sudah Sampai & Terkirim)
                    </button>
                </form>

                <form method="POST" action="{{ route('distribution-runs.destinations.update', [$distributionRun, $currentDestination]) }}" class="text-center">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="status" value="skipped">
                    <button type="submit"
                            onclick="return confirm('Lewati tujuan ini? Gunakan hanya jika tidak bisa dikirim.');"
                            class="text-xs text-slate-400 hover:text-red-500 font-semibold cursor-pointer">
                        Lewati tujuan ini
                    </button>
                </form>
            </div>
        @elseif ($allDone)
            <form method="POST" action="{{ route('distribution-runs.complete', $distributionRun) }}" x-ref="autoComplete">
                @csrf
            </form>
            <div class="bg-white p-6 rounded-2xl border border-slate-200 shadow-sm text-center space-y-2">
                <p class="text-sm text-slate-600">Semua tujuan sudah diproses. Menyelesaikan distribusi...</p>
            </div>
        @endif

    {{-- STEP 4: selesai --}}
    @elseif ($distributionRun->status === 'completed')
        <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm text-center space-y-3">
            <div class="w-16 h-16 mx-auto rounded-full bg-emerald-50 text-emerald-600 flex items-center justify-center">
                <svg class="w-8 h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h3 class="text-lg font-black text-slate-800">Distribusi Selesai</h3>
            <p class="text-sm text-slate-500">{{ number_format($distributionRun->deliveredPortions()) }} porsi terkirim ke {{ $totalDestinations }} tujuan.</p>
        </div>

    {{-- STEP 5: dibatalkan --}}
    @elseif ($distributionRun->status === 'cancelled')
        <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-sm text-center space-y-2">
            <h3 class="text-lg font-black text-slate-800">Distribusi Dibatalkan</h3>
        </div>
    @endif

    <div class="text-center">
        <a href="{{ route('distribution-runs.index') }}" class="text-xs text-slate-400 hover:text-slate-600 font-semibold">&larr; Kembali ke daftar distribusi</a>
    </div>
</div>
