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
