<x-layouts.app title="Detail Rute Greedy: {{ $routePlan->code }}" breadcrumb="Operasional / Monitoring Rute / Detail">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Top Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-800 font-bold flex items-center justify-center text-lg shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-bold text-slate-800 font-mono">{{ $routePlan->code }}</h2>
                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-md text-xs font-semibold bg-emerald-100 text-emerald-800 border border-emerald-200">
                            {{ str($routePlan->algorithm)->replace('_', ' ')->title() }}
                        </span>
                    </div>
                    <p class="text-xs text-slate-500">
                        Distribusi: <a href="{{ route('distribution-runs.show', $routePlan->run) }}" class="font-bold text-indigo-600 hover:underline">{{ $routePlan->run->code }}</a> &bull; Tanggal: {{ $routePlan->run->schedule->scheduled_date->format('d M Y') }} &bull; Petugas: {{ $routePlan->run->officer->name }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('route-plans.index') }}">
                    &larr; Daftar Rute
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route('distribution-runs.show', $routePlan->run) }}">
                    Buka Eksekusi Run &rarr;
                </x-button>
            </div>
        </div>

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Jarak Tempuh</div>
                    <div class="text-2xl font-black text-slate-800">{{ number_format($routePlan->total_distance_km, 2) }} <span class="text-sm font-normal text-slate-500">km</span></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Estimasi Waktu Perjalanan</div>
                    <div class="text-2xl font-black text-slate-800">{{ $routePlan->total_estimated_minutes }} <span class="text-sm font-normal text-slate-500">menit</span></div>
                </div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center gap-3.5">
                <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0 font-bold">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                </div>
                <div>
                    <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Titik Tujuan Sekolah</div>
                    <div class="text-2xl font-black text-slate-800">{{ $routePlan->steps->where('step_type', 'destination')->count() }} <span class="text-sm font-normal text-slate-500">lokasi</span></div>
                </div>
            </div>
        </div>

        <!-- Interactive Map Card -->
        <x-card title="Peta Rute Distribusi (OpenStreetMap + Leaflet)" subtitle="Garis biru menghubungkan urutan kunjungan terpendek dari depot ke seluruh titik sekolah">
            @php
                $markers = [];
                $polyline = [];
                foreach ($routePlan->steps as $step) {
                    $markers[] = [
                        'lat' => (float) $step->location->latitude,
                        'lng' => (float) $step->location->longitude,
                        'title' => $step->location->name,
                        'popup' => '<strong>' . ($step->step_type === 'start' ? 'Depot Dapur: ' : 'Urutan #' . $step->step_order . '. ') . $step->location->name . '</strong><br>' . ($step->runDestination?->recipient?->name ?: '') . '<br>Jarak dari titik sebelumnya: <b>' . $step->distance_from_previous_km . ' km</b><br>' . ($step->location->address ?: ''),
                        'type' => $step->step_type === 'start' ? 'depot' : 'destination',
                        'order' => $step->step_order,
                    ];
                    $polyline[] = [(float) $step->location->latitude, (float) $step->location->longitude];
                }

                $officerMarker = $routePlan->run->latestOfficerPosition ? [
                    'lat' => (float) $routePlan->run->latestOfficerPosition->latitude,
                    'lng' => (float) $routePlan->run->latestOfficerPosition->longitude,
                    'accuracy' => (float) ($routePlan->run->latestOfficerPosition->accuracy_meters ?? 0),
                    'popup' => '<strong>Posisi Petugas (' . $routePlan->run->officer->name . ')</strong><br>Update Terakhir: ' . $routePlan->run->latestOfficerPosition->recorded_at->format('d/m/Y H:i:s'),
                ] : null;
            @endphp

            <x-map id="route-map" :markers="$markers" :polyline="$polyline" :officer="$officerMarker" height="460px"
                :live-position-url="$routePlan->run->status === 'in_progress' ? route('distribution-runs.positions.latest', $routePlan->run) : null" />

            <div class="mt-4 flex flex-wrap items-center justify-between gap-3 text-xs text-slate-500 border-t border-slate-100 pt-3">
                <div class="flex items-center gap-4">
                    <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-full bg-indigo-600 text-white font-bold text-[9px] flex items-center justify-center">D</span> Depot Asal</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3.5 h-3.5 rounded-full bg-emerald-500 text-white font-bold text-[9px] flex items-center justify-center">#</span> Urutan Kunjungan</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-3 h-3 rounded-full bg-red-600 inline-block"></span> Posisi Petugas (Live GPS)</span>
                    <span class="inline-flex items-center gap-1.5"><span class="w-4 h-1 bg-blue-600 inline-block rounded"></span> Garis Jalur Greedy</span>
                </div>
                <div>
                    <a href="{{ route('route-plans.map-data', $routePlan) }}" target="_blank" class="text-indigo-600 font-semibold hover:underline flex items-center gap-1">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"></path></svg>
                        API JSON Map Data
                    </a>
                </div>
            </div>
        </x-card>

        <!-- Algorithm Process: animated visualization + full log -->
        @if (!empty($routePlan->algorithm_trace))
            @php
                $depot = $routePlan->run->schedule->depot;
                $coordsByName = [];
                foreach ($routePlan->steps as $step) {
                    $coordsByName[$step->location->name] = [
                        'lat' => (float) $step->location->latitude,
                        'lng' => (float) $step->location->longitude,
                    ];
                }
                $greedyTrace = collect($routePlan->algorithm_trace)->where('phase', 'greedy')->values();
                $twoOptTrace = collect($routePlan->algorithm_trace)->where('phase', 'two_opt')->values();
            @endphp

            <x-card title="Proses Algoritma Greedy (Animasi)" subtitle="Visualisasi langkah-langkah algoritma memilih tujuan terdekat berikutnya, lalu memperbaiki urutan dengan 2-opt">
                <div x-data="{
                        trace: @json($routePlan->algorithm_trace),
                        coords: @json($coordsByName),
                        depot: @json(['name' => $depot->name, 'lat' => (float) $depot->latitude, 'lng' => (float) $depot->longitude]),
                        currentIndex: 0,
                        currentOrder: [],
                        playing: false,
                        timer: null,
                        map: null,
                        markersLayer: null,
                        lineLayer: null,
                        get totalSteps() { return this.trace.length; },
                        get currentEntry() { return this.currentIndex > 0 ? this.trace[this.currentIndex - 1] : null; },
                        init() {
                            this.$nextTick(() => {
                                if (typeof L === 'undefined') return;
                                this.map = L.map(this.$refs.traceMap).setView([this.depot.lat, this.depot.lng], 14);
                                L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                    maxZoom: 19,
                                    attribution: '&copy; OpenStreetMap'
                                }).addTo(this.map);
                                this.markersLayer = L.layerGroup().addTo(this.map);
                                this.lineLayer = L.layerGroup().addTo(this.map);
                                this.render();
                                setTimeout(() => this.map.invalidateSize(), 300);
                            });
                        },
                        applyEntry(entry) {
                            if (entry.phase === 'greedy') {
                                this.currentOrder.push(entry.selected.name);
                            } else if (entry.phase === 'two_opt') {
                                this.currentOrder = entry.order_after.slice();
                            }
                        },
                        render() {
                            this.currentOrder = [];
                            for (let k = 0; k < this.currentIndex; k++) this.applyEntry(this.trace[k]);

                            this.markersLayer.clearLayers();
                            this.lineLayer.clearLayers();

                            const depotIcon = L.divIcon({
                                className: 'custom-map-marker',
                                html: '<div style=\'background:#4f46e5;width:26px;height:26px;border-radius:50%;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:11px;\'>D</div>',
                                iconSize: [26, 26], iconAnchor: [13, 13]
                            });
                            L.marker([this.depot.lat, this.depot.lng], { icon: depotIcon }).addTo(this.markersLayer)
                                .bindPopup(`<strong>${this.depot.name}</strong><br>Depot Asal`);

                            const points = [[this.depot.lat, this.depot.lng]];
                            this.currentOrder.forEach((name, idx) => {
                                const c = this.coords[name];
                                if (!c) return;
                                points.push([c.lat, c.lng]);
                                const icon = L.divIcon({
                                    className: 'custom-map-marker',
                                    html: `<div style='background:#10b981;width:24px;height:24px;border-radius:50%;border:2px solid white;box-shadow:0 2px 4px rgba(0,0,0,.3);display:flex;align-items:center;justify-content:center;color:white;font-weight:bold;font-size:11px;'>${idx + 1}</div>`,
                                    iconSize: [24, 24], iconAnchor: [12, 12]
                                });
                                L.marker([c.lat, c.lng], { icon }).addTo(this.markersLayer).bindPopup(`<strong>#${idx + 1} ${name}</strong>`);
                            });

                            if (points.length > 1) {
                                const style = { color: '#2563eb', weight: 4, opacity: 0.85 };
                                if (this.currentEntry?.phase === 'two_opt') style.dashArray = '6,6';
                                L.polyline(points, style).addTo(this.lineLayer);
                            }

                            if (points.length > 1) {
                                this.map.fitBounds(points, { padding: [30, 30] });
                            } else {
                                this.map.setView(points[0], 14);
                            }
                        },
                        next() {
                            if (this.currentIndex < this.totalSteps) { this.currentIndex++; this.render(); }
                            else { this.pause(); }
                        },
                        prev() {
                            if (this.currentIndex > 0) { this.currentIndex--; this.render(); }
                        },
                        play() {
                            if (this.playing) return;
                            this.playing = true;
                            this.timer = setInterval(() => this.next(), 1600);
                        },
                        pause() {
                            this.playing = false;
                            if (this.timer) clearInterval(this.timer);
                            this.timer = null;
                        },
                        reset() {
                            this.pause();
                            this.currentIndex = 0;
                            this.render();
                        },
                        describeCurrentStep() {
                            const e = this.currentEntry;
                            if (!e) return 'Belum dimulai. Titik awal: Depot ' + this.depot.name + '.';
                            if (e.phase === 'greedy') {
                                return `Langkah ${e.step} (Greedy): dari '${e.from.name}', kandidat terdekat yang dipilih adalah '${e.selected.name}' (${e.selected.distance_km} km).`;
                            }
                            return `Perbaikan 2-opt #${e.step}: menukar urutan segmen ke-${e.segment[0]} s/d ke-${e.segment[1]} — total jarak turun dari ${e.before_km} km menjadi ${e.after_km} km.`;
                        }
                    }" class="space-y-4">

                    <div x-ref="traceMap" class="w-full rounded-2xl overflow-hidden border border-slate-200/80" style="height: 380px;"></div>

                    <div class="bg-slate-50 border border-slate-200 rounded-xl px-4 py-3 text-sm text-slate-700 font-medium min-h-[3rem] flex items-center" x-text="describeCurrentStep()"></div>

                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <button type="button" @click="reset()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer">&laquo; Ulang</button>
                            <button type="button" @click="prev()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer">&lsaquo; Sebelumnya</button>
                            <button type="button" @click="playing ? pause() : play()" class="px-4 py-1.5 rounded-lg text-xs font-bold text-white bg-indigo-600 hover:bg-indigo-500 cursor-pointer">
                                <span x-text="playing ? 'Jeda' : 'Putar Otomatis'"></span>
                            </button>
                            <button type="button" @click="next()" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-slate-100 hover:bg-slate-200 text-slate-700 cursor-pointer">Berikutnya &rsaquo;</button>
                        </div>
                        <div class="text-xs font-semibold text-slate-500">
                            Langkah <span x-text="currentIndex"></span> / <span x-text="totalSteps"></span>
                        </div>
                    </div>
                </div>
            </x-card>

            <!-- Full Algorithm Log Tables -->
            <x-card title="Log Lengkap Iterasi Nearest-Neighbor" subtitle="Kandidat yang dipertimbangkan di setiap langkah dan alasan pemilihan (jarak terpendek)">
                <div class="space-y-3">
                    @foreach ($greedyTrace as $entry)
                        <div class="border border-slate-200 rounded-xl overflow-hidden">
                            <div class="bg-slate-50 px-4 py-2.5 text-xs font-bold text-slate-700 flex items-center justify-between">
                                <span>Langkah {{ $entry['step'] }} &mdash; dari "{{ $entry['from']['name'] }}"</span>
                                <span class="text-emerald-700">Terpilih: {{ $entry['selected']['name'] }} ({{ number_format($entry['selected']['distance_km'], 2) }} km)</span>
                            </div>
                            <x-table :headers="['Kandidat Tujuan', 'Jarak (km)', 'Status']">
                                @foreach ($entry['candidates'] as $candidate)
                                    <tr class="{{ $candidate['name'] === $entry['selected']['name'] ? 'bg-emerald-50/60' : '' }}">
                                        <td class="px-4 py-2 text-sm font-medium text-slate-700">{{ $candidate['name'] }}</td>
                                        <td class="px-4 py-2 text-sm font-mono text-slate-600">{{ number_format($candidate['distance_km'], 3) }}</td>
                                        <td class="px-4 py-2 text-xs">
                                            @if ($candidate['name'] === $entry['selected']['name'])
                                                <span class="inline-flex items-center px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-700 font-bold">Dipilih (terdekat)</span>
                                            @else
                                                <span class="text-slate-400">Tidak dipilih</span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </x-table>
                        </div>
                    @endforeach
                </div>
            </x-card>

            @if ($twoOptTrace->isNotEmpty())
                <x-card title="Log Perbaikan 2-opt" subtitle="Pertukaran urutan yang diterapkan karena terbukti memperpendek total jarak">
                    <x-table :headers="['Perbaikan #', 'Segmen Ditukar', 'Jarak Sebelum', 'Jarak Sesudah', 'Selisih']">
                        @foreach ($twoOptTrace as $entry)
                            <tr>
                                <td class="px-4 py-2.5 text-sm font-bold text-slate-700">#{{ $entry['step'] }}</td>
                                <td class="px-4 py-2.5 text-sm font-mono text-slate-600">urutan ke-{{ $entry['segment'][0] }} s/d ke-{{ $entry['segment'][1] }}</td>
                                <td class="px-4 py-2.5 text-sm font-mono text-slate-600">{{ number_format($entry['before_km'], 3) }} km</td>
                                <td class="px-4 py-2.5 text-sm font-mono text-emerald-700 font-bold">{{ number_format($entry['after_km'], 3) }} km</td>
                                <td class="px-4 py-2.5 text-sm font-mono text-red-600">-{{ number_format($entry['before_km'] - $entry['after_km'], 3) }} km</td>
                            </tr>
                        @endforeach
                    </x-table>
                </x-card>
            @else
                <x-card title="Log Perbaikan 2-opt" subtitle="Tidak ada perbaikan yang diterapkan">
                    <p class="text-sm text-slate-500">Urutan hasil Greedy nearest-neighbor pada rute ini sudah optimal &mdash; tidak ada pertukaran 2-opt yang mengurangi jarak.</p>
                </x-card>
            @endif
        @else
            <x-card title="Proses Algoritma Greedy" subtitle="Log proses belum tersedia">
                <p class="text-sm text-slate-500">Rute ini dibuat sebelum fitur pencatatan proses algoritma ditambahkan. Klik <strong>"Generate Ulang Rute Greedy"</strong> di halaman distribusi untuk melihat log proses lengkap.</p>
            </x-card>
        @endif

        <!-- Steps Table -->
        <x-card title="Urutan Perjalanan & Jarak Antar Titik" subtitle="Rincian langkah pergerakan armada logistik dari depot ke setiap titik sekolah">
            <x-table :headers="['Urutan', 'Tipe Titik', 'Nama Lokasi', 'Sekolah / Penerima', 'Koordinat GPS', 'Jarak dari Sebelumnya', 'Jarak Kumulatif']">
                @foreach ($routePlan->steps as $step)
                    <tr class="hover:bg-slate-50/80 transition-colors">
                        <td class="px-4 py-3.5 whitespace-nowrap font-mono font-bold text-slate-700 text-sm">
                            {{ $step->step_type === 'start' ? 'Start' : '#' . $step->step_order }}
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            @if ($step->step_type === 'start')
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-indigo-100 text-indigo-800 border border-indigo-200">
                                    Depot Asal (Kitchen)
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                                    Tujuan Sekolah
                                </span>
                            @endif
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <div class="font-bold text-slate-800 text-sm">{{ $step->location->name }}</div>
                            <div class="text-[11px] text-slate-500 truncate max-w-xs">{{ $step->location->address ?: '-' }}</div>
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap font-semibold text-slate-700 text-sm">
                            {{ $step->runDestination?->recipient?->name ?? '-' }}
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs text-slate-500">
                            {{ number_format((float)$step->location->latitude, 5) }}, {{ number_format((float)$step->location->longitude, 5) }}
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-bold text-slate-700 text-sm">{{ number_format($step->distance_from_previous_km, 2) }}</span> <span class="text-xs text-slate-400">km</span>
                        </td>

                        <td class="px-4 py-3.5 whitespace-nowrap">
                            <span class="font-black text-indigo-900 bg-indigo-50 px-2.5 py-1 rounded-lg border border-indigo-100 text-sm">
                                {{ number_format($step->cumulative_distance_km, 2) }} <span class="text-xs font-normal text-slate-500">km</span>
                            </span>
                        </td>
                    </tr>
                @endforeach
            </x-table>
        </x-card>

    </div>
</x-layouts.app>
