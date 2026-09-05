<x-layouts.app title="Monitoring Run: {{ $distributionRun->code }}" breadcrumb="Operasional / Distribusi Aktual / Detail">
    @php
        // Field execution (Start/Complete/Cancel, delivery status updates, GPS position)
        // is exclusive to the officer actually assigned to this run - not admin, and
        // not any other petugas who happens to open this page. Admin keeps the
        // separate ability to generate/regenerate the route (route planning is an
        // operational-setup action, not a field action).
        $isAssignedOfficer = auth()->user()->hasRole('petugas') && auth()->user()->officer?->id === $distributionRun->officer_id;
        $canGenerateRoute = auth()->user()->hasRole('admin') || $isAssignedOfficer;
        $canExecuteField = $isAssignedOfficer;
    @endphp
    <div x-data="{
        polling: false,
        lastUpdated: '{{ now()->format('H:i:s') }}',
        errorMsg: null,
        autoRefresh: {{ $distributionRun->status === 'in_progress' ? 'true' : 'false' }},
        init() {
            if (this.autoRefresh) {
                this.startPolling();
            }
        },
        startPolling() {
            setInterval(async () => {
                if (!this.autoRefresh || this.polling) return;
                this.polling = true;
                try {
                    const res = await fetch('{{ route('api.frontend.distribution-runs.show', $distributionRun) }}');
                    if (res.ok) {
                        this.lastUpdated = new Date().toLocaleTimeString('id-ID');
                        this.errorMsg = null;
                        // Reload window lightly or notify if status changed
                        const data = await res.json();
                        if (data.status !== '{{ $distributionRun->status }}') {
                            window.location.reload();
                        }
                    } else {
                        this.errorMsg = 'Gagal memuat pembaruan data real-time (HTTP ' + res.status + ').';
                    }
                } catch (err) {
                    this.errorMsg = 'Koneksi ke server terputus. Mencoba kembali otomatis...';
                } finally {
                    this.polling = false;
                }
            }, 8000);
        }
    }" class="max-w-7xl mx-auto space-y-6">

        <!-- Realtime Monitoring Banner -->
        <div class="bg-slate-900 text-white px-4 py-2.5 rounded-xl border border-slate-800 flex flex-wrap items-center justify-between gap-3 text-xs shadow-sm">
            <div class="flex items-center gap-2.5">
                <span class="relative flex h-2.5 w-2.5">
                  <span x-show="autoRefresh && !errorMsg" class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                  <span :class="errorMsg ? 'bg-red-500' : (autoRefresh ? 'bg-emerald-500' : 'bg-slate-500')" class="relative inline-flex rounded-full h-2.5 w-2.5"></span>
                </span>
                <span class="font-bold tracking-wide" x-text="autoRefresh ? 'MONITORING REALTIME AKTIF (Polling tiap 8s)' : 'MONITORING STATIS (Refresh manual)'"></span>
                <span x-show="errorMsg" x-text="errorMsg" class="text-red-400 font-semibold bg-red-950/80 px-2 py-0.5 rounded border border-red-800 animate-pulse" style="display: none;"></span>
            </div>
            <div class="flex items-center gap-4 text-slate-400">
                <span>Terakhir diperbarui: <strong class="text-white font-mono" x-text="lastUpdated"></strong></span>
                <button @click="autoRefresh = !autoRefresh; if(autoRefresh) startPolling();" 
                        class="px-2.5 py-1 rounded bg-slate-800 hover:bg-slate-700 text-white font-semibold transition-colors">
                    <span x-text="autoRefresh ? 'Jeda Polling' : 'Aktifkan Polling'"></span>
                </button>
            </div>
        </div>
        
        <!-- Top Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-blue-600 text-white font-bold flex items-center justify-center text-lg shadow-md shadow-blue-900/20">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-black text-slate-800 font-mono">{{ $distributionRun->code }}</h2>
                        @php
                            $statusBadge = match($distributionRun->status) {
                                'ready' => 'default',
                                'in_progress' => 'active',
                                'completed' => 'active',
                                'cancelled' => 'inactive',
                                default => 'default',
                            };
                            $statusName = match($distributionRun->status) {
                                'ready' => 'Siap Kirim (Ready)',
                                'in_progress' => 'Sedang Berjalan (In Progress)',
                                'completed' => 'Selesai Terkirim',
                                'cancelled' => 'Dibatalkan',
                                default => str($distributionRun->status)->replace('_', ' ')->title(),
                            };
                        @endphp
                        <x-badge :variant="$statusBadge">
                            {{ $statusName }}
                        </x-badge>
                    </div>
                    <p class="text-xs text-slate-500">
                        Jadwal: <a href="{{ route('distribution-schedules.show', $distributionRun->schedule) }}" class="font-bold text-indigo-600 hover:underline">{{ $distributionRun->schedule->code }}</a> &bull; Tanggal: {{ $distributionRun->schedule->scheduled_date->format('d M Y') }} &bull; Depot: {{ $distributionRun->schedule->depot->name }}
                    </p>
                </div>
            </div>

            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('distribution-runs.index') }}">
                    &larr; Kembali
                </x-button>

                @if ($distributionRun->routePlan)
                    <x-button variant="primary" size="sm" href="{{ route('route-plans.show', $distributionRun->routePlan) }}" class="bg-indigo-600 hover:bg-indigo-700">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                        Lihat Detail Peta Greedy
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Porsi Terkirim</div>
                    <div class="text-xl font-black text-slate-800">{{ number_format($distributionRun->deliveredPortions()) }} <span class="text-xs font-normal text-slate-500">/ {{ number_format($distributionRun->destinations->sum('planned_portion_count')) }}</span></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Jarak (Greedy)</div>
                    <div class="text-xl font-black text-slate-800">{{ $distributionRun->routePlan ? number_format($distributionRun->routePlan->total_distance_km, 1) . ' km' : '-' }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Waktu</div>
                    <div class="text-xl font-black text-slate-800">{{ $distributionRun->routePlan ? $distributionRun->routePlan->total_estimated_minutes . ' Mnt' : '-' }}</div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Petugas Pengirim</div>
                    <div class="text-base font-bold text-slate-800 truncate">{{ $distributionRun->officer->name }}</div>
                </div>
            </div>
        </div>

        <!-- Action Panel: Generate Rute (admin or assigned officer) / field actions (assigned officer only) -->
        @if ($canGenerateRoute || $canExecuteField)
            <div class="bg-slate-900 text-white p-5 rounded-2xl shadow-lg border border-slate-800 flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3.5">
                    <div class="w-10 h-10 rounded-xl bg-emerald-500/20 text-emerald-400 flex items-center justify-center shrink-0">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-white">Kontrol Eksekusi & Algoritma Rute</h4>
                        <p class="text-xs text-slate-400">Kelola status pengiriman di lapangan atau hitung ulang rute terpendek dengan Greedy Nearest Neighbor</p>
                    </div>
                </div>

                <div class="flex flex-wrap items-center gap-2.5 w-full md:w-auto justify-end">
                    <!-- Generate Route Greedy Button (Admin or the assigned officer) -->
                    @if ($canGenerateRoute)
                        <form method="POST" action="{{ route('distribution-runs.route-plan.generate', $distributionRun) }}" class="inline">
                            @csrf
                            <button type="submit"
                                    onclick="return confirm('Generate rute terpendek dengan algoritma Greedy Nearest Neighbor dari titik depot?');"
                                    class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 transition-colors shadow-sm cursor-pointer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                                {{ $distributionRun->routePlan ? 'Generate Ulang Rute Greedy' : 'Generate Rute Greedy' }}
                            </button>
                        </form>
                    @endif

                    <!-- Field actions: Start / Complete / Cancel are exclusive to the assigned officer -->
                    @if ($canExecuteField)
                        <!-- Start Button (ready only) -->
                        @if ($distributionRun->status === 'ready')
                            <form method="POST" action="{{ route('distribution-runs.start', $distributionRun) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Mulai keberangkatan distribusi sekarang? Status akan berubah menjadi In Progress.');"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-emerald-600 hover:bg-emerald-500 transition-colors shadow-sm cursor-pointer animate-bounce">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Mulai Distribusi (Start)
                                </button>
                            </form>
                        @endif

                        <!-- Complete Button (in_progress only) -->
                        @if ($distributionRun->status === 'in_progress')
                            <form method="POST" action="{{ route('distribution-runs.complete', $distributionRun) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Apakah semua pengiriman sudah selesai? Selesaikan distribusi ini?');"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-blue-600 hover:bg-blue-500 transition-colors shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                    Selesaikan Distribusi (Complete)
                                </button>
                            </form>
                        @endif

                        <!-- Cancel Button -->
                        @if (! in_array($distributionRun->status, ['completed', 'cancelled'], true))
                            <form method="POST" action="{{ route('distribution-runs.cancel', $distributionRun) }}" class="inline">
                                @csrf
                                <button type="submit"
                                        onclick="return confirm('Batalkan distribusi ini? Action ini tidak dapat dibatalkan.');"
                                        class="inline-flex items-center gap-1.5 px-3 py-2 rounded-xl text-xs font-semibold text-slate-300 hover:text-red-400 hover:bg-slate-800 transition-colors cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    Batalkan Run
                                </button>
                            </form>
                        @endif
                    @endif
                </div>
            </div>
        @elseif (auth()->user()->hasRole('petugas'))
            <div class="bg-slate-50 border border-dashed border-slate-200 rounded-2xl px-4 py-3 text-xs text-slate-500">
                Distribusi ini ditugaskan ke petugas lain (<strong>{{ $distributionRun->officer->name }}</strong>) &mdash; Anda hanya bisa memantau, tidak bisa mengubah statusnya.
            </div>
        @endif

        <!-- Live Map Preview & GPS Update Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Map Preview Column (2 Cols if exists) -->
            <div class="{{ $distributionRun->status === 'in_progress' && $canExecuteField ? 'lg:col-span-2' : 'lg:col-span-3' }}">
                <x-card title="Peta Monitoring & Rute Greedy" subtitle="Visualisasi titik pengiriman dan posisi petugas real-time">
                    @php
                        $markers = [];
                        $polyline = [];
                        if ($distributionRun->routePlan) {
                            foreach ($distributionRun->routePlan->steps as $step) {
                                $markers[] = [
                                    'lat' => (float) $step->location->latitude,
                                    'lng' => (float) $step->location->longitude,
                                    'title' => $step->location->name,
                                    'popup' => '<strong>' . ($step->step_type === 'start' ? 'Depot: ' : $step->step_order . '. ') . $step->location->name . '</strong><br>' . ($step->runDestination?->recipient?->name ?: '') . '<br>Jarak: ' . $step->distance_from_previous_km . ' km',
                                    'type' => $step->step_type === 'start' ? 'depot' : 'destination',
                                    'order' => $step->step_order,
                                ];
                                $polyline[] = [(float) $step->location->latitude, (float) $step->location->longitude];
                            }
                        } else {
                            // Add depot and destinations without order if route not generated yet
                            if ($distributionRun->schedule->depot->latitude && $distributionRun->schedule->depot->longitude) {
                                $markers[] = [
                                    'lat' => (float) $distributionRun->schedule->depot->latitude,
                                    'lng' => (float) $distributionRun->schedule->depot->longitude,
                                    'title' => 'Depot: ' . $distributionRun->schedule->depot->name,
                                    'popup' => '<strong>Depot Asal: ' . $distributionRun->schedule->depot->name . '</strong>',
                                    'type' => 'depot',
                                ];
                            }
                            foreach ($distributionRun->destinations as $idx => $dest) {
                                if ($dest->location->latitude && $dest->location->longitude) {
                                    $markers[] = [
                                        'lat' => (float) $dest->location->latitude,
                                        'lng' => (float) $dest->location->longitude,
                                        'title' => $dest->location->name,
                                        'popup' => '<strong>' . ($idx + 1) . '. ' . $dest->location->name . '</strong><br>Penerima: ' . $dest->recipient->name,
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
                            'popup' => '<strong>Posisi Petugas (' . $distributionRun->officer->name . ')</strong><br>Waktu: ' . $distributionRun->latestOfficerPosition->recorded_at->format('d/m/Y H:i:s'),
                        ] : null;
                    @endphp

                    @if (!empty($markers))
                        <x-map :markers="$markers" :polyline="$polyline" :officer="$officerMarker" height="380px"
                            :live-position-url="$distributionRun->status === 'in_progress' ? route('distribution-runs.positions.latest', $distributionRun) : null" />
                    @else
                        <div class="p-8 text-center bg-slate-50 rounded-2xl border border-dashed border-slate-200 text-slate-500 text-sm">
                            Koordinat GPS untuk depot dan sekolah tujuan belum melengkapi data. Silakan lengkapi di menu Lokasi & Depot.
                        </div>
                    @endif

                    <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500 border-t border-slate-100 pt-3">
                        <div class="flex items-center gap-4">
                            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-indigo-600 inline-block"></span> Depot Asal</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-emerald-500 inline-block"></span> Tujuan Sekolah</span>
                            <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span> Posisi Petugas (Live GPS)</span>
                        </div>
                        <div>
                            <a href="{{ route('distribution-runs.positions.latest', $distributionRun) }}" target="_blank" class="text-indigo-600 font-semibold hover:underline">Lihat JSON Log Posisi</a>
                        </div>
                    </div>
                </x-card>
            </div>

            <!-- GPS Live Updater Column (For Petugas during in_progress) -->
            @if ($distributionRun->status === 'in_progress' && $canExecuteField)
                <div class="space-y-6">
                    <x-card title="Update Posisi Petugas (GPS)" subtitle="Kirim koordinat real-time ke sistem monitoring">
                        <div x-data="{
                                tracking: false,
                                watchId: null,
                                sending: false,
                                lastSentAt: null,
                                error: null,
                                storeUrl: '{{ route('distribution-runs.positions.store', $distributionRun) }}',
                                toggle() {
                                    this.tracking ? this.stop() : this.start();
                                },
                                start() {
                                    if (!navigator.geolocation) {
                                        this.error = 'Browser Anda tidak mendukung Geolocation.';
                                        return;
                                    }
                                    this.tracking = true;
                                    this.error = null;
                                    this.watchId = navigator.geolocation.watchPosition(
                                        pos => this.send(pos),
                                        err => { this.error = 'Gagal melacak GPS: ' + err.message; },
                                        { enableHighAccuracy: true, maximumAge: 8000, timeout: 15000 }
                                    );
                                },
                                stop() {
                                    if (this.watchId !== null) navigator.geolocation.clearWatch(this.watchId);
                                    this.watchId = null;
                                    this.tracking = false;
                                },
                                async send(pos) {
                                    if (this.sending) return;
                                    this.sending = true;
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
                                        if (res.ok) {
                                            this.lastSentAt = new Date().toLocaleTimeString('id-ID');
                                            this.error = null;
                                        } else {
                                            this.error = 'Gagal mengirim posisi (HTTP ' + res.status + ').';
                                        }
                                    } catch (e) {
                                        this.error = 'Koneksi terputus saat mengirim posisi.';
                                    } finally {
                                        this.sending = false;
                                    }
                                }
                            }" class="mb-4 p-3 rounded-xl border border-dashed border-indigo-200 bg-indigo-50/50 space-y-2">
                            <button type="button" @click="toggle()"
                                    :class="tracking ? 'bg-red-600 hover:bg-red-700' : 'bg-indigo-600 hover:bg-indigo-700'"
                                    class="w-full py-2 px-3 rounded-xl text-white text-xs font-bold flex items-center justify-center gap-2 transition-colors cursor-pointer">
                                <span x-show="tracking" class="relative flex h-2 w-2" style="display:none">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                                </span>
                                <span x-text="tracking ? 'Matikan Pelacakan Otomatis' : 'Aktifkan Pelacakan Otomatis (Live)'"></span>
                            </button>
                            <p class="text-[11px] text-slate-500" x-show="tracking">
                                Posisi terkirim otomatis setiap perangkat mendeteksi pergerakan. Terakhir terkirim: <span class="font-mono font-semibold" x-text="lastSentAt || '-'"></span>
                            </p>
                            <p class="text-[11px] text-red-600 font-semibold" x-show="error" x-text="error" style="display:none"></p>
                        </div>

                        <form method="POST" action="{{ route('distribution-runs.positions.store', $distributionRun) }}"
                              x-data="{
                                  lat: '{{ old('latitude', $distributionRun->latestOfficerPosition->latitude ?? '') }}',
                                  lng: '{{ old('longitude', $distributionRun->latestOfficerPosition->longitude ?? '') }}',
                                  acc: '{{ old('accuracy_meters', $distributionRun->latestOfficerPosition->accuracy_meters ?? '15') }}',
                                  fetching: false,
                                  getGps() {
                                      if (!navigator.geolocation) {
                                          alert('Browser Anda tidak mendukung Geolocation.');
                                          return;
                                      }
                                      this.fetching = true;
                                      navigator.geolocation.getCurrentPosition(
                                          pos => {
                                              this.lat = pos.coords.latitude.toFixed(7);
                                              this.lng = pos.coords.longitude.toFixed(7);
                                              this.acc = pos.coords.accuracy.toFixed(1);
                                              this.fetching = false;
                                          },
                                          err => {
                                              alert('Gagal mengambil GPS: ' + err.message);
                                              this.fetching = false;
                                          },
                                          { enableHighAccuracy: true, timeout: 10000 }
                                      );
                                  }
                              }" class="space-y-4">
                            @csrf

                            <button type="button" @click="getGps()" 
                                    class="w-full py-2 px-3 rounded-xl bg-indigo-50 border border-indigo-200 text-indigo-700 hover:bg-indigo-100 text-xs font-bold flex items-center justify-center gap-2 transition-colors cursor-pointer">
                                <svg class="w-4 h-4 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span x-text="fetching ? 'Mendeteksi Posisi GPS...' : 'Dapatkan Koordinat GPS Saya (Otomatis)'"></span>
                            </button>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Latitude</label>
                                <input name="latitude" type="number" step="0.0000001" x-model="lat" required placeholder="-6.2088..." class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Longitude</label>
                                <input name="longitude" type="number" step="0.0000001" x-model="lng" required placeholder="106.8456..." class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-slate-700 mb-1">Akurasi (Meter)</label>
                                <input name="accuracy_meters" type="number" step="0.01" min="0" x-model="acc" class="w-full px-3 py-1.5 text-xs rounded-lg border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-mono">
                            </div>

                            <x-button type="submit" variant="primary" size="sm" class="w-full justify-center mt-2">
                                Kirim Posisi ke Server
                            </x-button>
                        </form>
                    </x-card>
                </div>
            @endif

        </div>

        <!-- Destinations Execution Table -->
        <x-card title="Eksekusi Pengiriman per Tujuan Sekolah" subtitle="Daftar sekolah penerima paket MBG sesuai urutan jadwal">
            <x-table :headers="['Urutan', 'Sekolah / Penerima', 'Rencana', 'Terkirim', 'Status Pengiriman', 'Waktu', 'Aksi / Bukti Lapangan']">
                @foreach ($distributionRun->destinations->sortBy('sequence_order') as $destination)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs font-bold text-slate-500">
                            #{{ $destination->sequence_order }}
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-bold text-slate-800 text-sm">{{ $destination->recipient->name ?? 'Penerima Terhapus' }}</div>
                            <div class="text-[11px] text-slate-500">{{ $destination->location->name ?? '-' }}</div>
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-bold text-slate-700 text-sm">{{ number_format($destination->planned_portion_count) }}</span> <span class="text-xs text-slate-400">porsi</span>
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if ($destination->delivered_portion_count !== null)
                                <span class="font-black text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200 text-sm">
                                    {{ number_format($destination->delivered_portion_count) }}
                                </span> <span class="text-xs text-slate-400">porsi</span>
                            @else
                                <span class="text-slate-400 text-xs italic">-</span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @php
                                $destBadge = match($destination->status) {
                                    'pending' => 'default',
                                    'arrived' => 'active',
                                    'delivered' => 'active',
                                    'skipped' => 'inactive',
                                    default => 'default',
                                };
                                $destLabel = match($destination->status) {
                                    'pending' => 'Pending (Belum Tiba)',
                                    'arrived' => 'Tiba di Sekolah',
                                    'delivered' => 'Berhasil Terkirim',
                                    'skipped' => 'Dilewati (Skipped)',
                                    default => str($destination->status)->replace('_', ' ')->title(),
                                };
                            @endphp
                            <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold 
                                @if($destination->status === 'delivered') bg-emerald-100 text-emerald-800 border border-emerald-200
                                @elseif($destination->status === 'arrived') bg-blue-100 text-blue-800 border border-blue-200
                                @elseif($destination->status === 'skipped') bg-red-100 text-red-800 border border-red-200
                                @else bg-slate-100 text-slate-600 border border-slate-200 @endif">
                                {{ $destLabel }}
                            </span>
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500">
                            <div><span class="font-semibold text-slate-400">Tiba:</span> {{ $destination->arrived_at?->format('H:i') ?? '-' }}</div>
                            <div><span class="font-semibold text-slate-400">Kirim:</span> {{ $destination->delivered_at?->format('H:i') ?? '-' }}</div>
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if ($distributionRun->status === 'in_progress' && $canExecuteField)
                                <form method="POST" action="{{ route('distribution-runs.destinations.update', [$distributionRun, $destination]) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PUT')

                                    <select name="status" required class="px-2 py-1 text-xs rounded-lg border border-slate-200 bg-white font-semibold text-slate-700 focus:ring-1 focus:ring-emerald-500">
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
                                        placeholder="Porsi"
                                        title="Jumlah porsi diserahkan"
                                        class="w-16 px-2 py-1 text-xs rounded-lg border border-slate-200 font-mono text-center focus:ring-1 focus:ring-emerald-500"
                                    >

                                    <input
                                        name="proof_notes"
                                        value="{{ old('proof_notes', $destination->proof_notes ?? '') }}"
                                        placeholder="Bukti / nama penerima"
                                        class="w-32 sm:w-44 px-2 py-1 text-xs rounded-lg border border-slate-200 focus:ring-1 focus:ring-emerald-500"
                                    >

                                    <button type="submit" class="px-2.5 py-1 rounded-lg bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs shadow-2xs transition-colors shrink-0 cursor-pointer">
                                        Update
                                    </button>
                                </form>
                            @else
                                <span class="text-xs text-slate-700 font-medium">{{ $destination->proof_notes ?: 'Tidak ada catatan bukti' }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

    </div>
</x-layouts.app>
