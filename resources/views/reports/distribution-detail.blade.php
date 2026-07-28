<x-layouts.app title="Detail Laporan Distribusi: {{ $run->code }}" breadcrumb="Laporan / Distribusi / Detail">
    <div class="max-w-7xl mx-auto space-y-6">
        
        <!-- Top Title Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-900 text-white font-bold flex items-center justify-center text-lg shadow-md">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h2 class="text-xl font-black text-slate-800 font-mono"><span class="text-slate-400 font-normal">Detail Laporan Distribusi:</span> {{ $run->code }}</h2>
                        @php
                            $statusBadge = match($run->status) {
                                'ready' => 'default',
                                'in_progress' => 'active',
                                'completed' => 'active',
                                'cancelled' => 'inactive',
                                default => 'default',
                            };
                            $statusName = match($run->status) {
                                'ready' => 'Siap Kirim',
                                'in_progress' => 'Sedang Berjalan',
                                'completed' => 'Selesai Terkirim',
                                'cancelled' => 'Dibatalkan',
                                default => str($run->status)->replace('_', ' ')->title(),
                            };
                        @endphp
                        <span class="text-xs text-slate-400 font-normal ml-1">Status akhir:</span>
                        <x-badge :variant="$statusBadge">{{ $statusName }}</x-badge>
                    </div>
                    <p class="text-xs text-slate-500">
                        Tanggal Jadwal: {{ $run->schedule->scheduled_date->format('d M Y') }} &bull; Depot: {{ $run->schedule->depot->name }} &bull; Petugas: {{ $run->officer->name }}
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('reports.distributions.index') }}">
                    &larr; Kembali ke Rekap Laporan
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route('distribution-runs.show', $run) }}">
                    Buka Monitoring Run &rarr;
                </x-button>
            </div>
        </div>

        <!-- Metrics Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3 sm:gap-4">
            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Total Tujuan</div>
                <div class="mt-1 text-lg font-black text-slate-800">{{ number_format($metrics['total_destinations']) }} <span class="text-xs font-normal text-slate-400">sekolah</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-emerald-600 uppercase">Tujuan selesai</div>
                <div class="mt-1 text-lg font-black text-emerald-600">{{ number_format($metrics['delivered_destinations']) }} <span class="text-xs font-normal text-slate-400">sekolah</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-red-600 uppercase">Tujuan Gagal</div>
                <div class="mt-1 text-lg font-black text-red-600">{{ number_format($metrics['failed_destinations']) }} <span class="text-xs font-normal text-slate-400">sekolah</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-indigo-600 uppercase">Porsi Terkirim</div>
                <div class="mt-1 text-lg font-black text-indigo-900">{{ number_format($metrics['delivered_portions']) }} <span class="text-[10px] font-normal text-slate-400">/ {{ number_format($metrics['planned_portions']) }}</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-blue-600 uppercase">Total Jarak</div>
                <div class="mt-1 text-lg font-black text-blue-700">{{ number_format($metrics['total_distance_km'], 1) }} <span class="text-xs font-normal text-slate-400">km</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-amber-600 uppercase">Estimasi Waktu</div>
                <div class="mt-1 text-lg font-black text-amber-600">{{ $metrics['estimated_minutes'] ?? '-' }} <span class="text-xs font-normal text-slate-400">mnt</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-blue-600 uppercase">Waktu aktual</div>
                <div class="mt-1 text-lg font-black text-blue-600">{{ $metrics['actual_duration_minutes'] ?? '-' }} <span class="text-xs font-normal text-slate-400">mnt</span></div>
            </div>

            <div class="bg-white p-3.5 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[10px] font-bold text-slate-400 uppercase">Catatan Run</div>
                <div class="mt-1 text-xs font-bold text-slate-700 truncate" title="{{ $run->notes }}">{{ $run->notes ?: '-' }}</div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            
            <!-- Destinations Table Column -->
            <div class="lg:col-span-2 space-y-6">
                <x-card title="Detail tujuan (Rincian Realisasi Tujuan Sekolah)" subtitle="Daftar penerima, waktu tiba, jumlah porsi, dan catatan lapangan">
                    <x-table :headers="['Urutan', 'Sekolah / Penerima', 'Status', 'Porsi', 'Waktu Tiba & Selesai', 'Bukti / Catatan']">
                        @foreach ($run->destinations->sortBy('sequence_order') as $destination)
                            <tr class="hover:bg-slate-50/80 transition-colors">
                                <td class="px-4 py-3.5 whitespace-nowrap font-mono text-xs font-bold text-slate-500">
                                    #{{ $destination->sequence_order }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap">
                                    <div class="font-bold text-slate-800 text-sm">{{ $destination->recipient->name }}</div>
                                    <div class="text-[11px] text-slate-500">{{ $destination->location->name }}</div>
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
                                        $destName = match($destination->status) {
                                            'pending' => 'Pending',
                                            'arrived' => 'Tiba di Lokasi',
                                            'delivered' => 'Terkirim',
                                            'skipped' => 'Dilewati',
                                            default => str($destination->status)->replace('_', ' ')->title(),
                                        };
                                    @endphp
                                    <x-badge :variant="$destBadge">{{ $destName }}</x-badge>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold text-slate-700">
                                    <span class="text-indigo-600 font-bold">{{ number_format($destination->delivered_portion_count ?? 0) }}</span> / {{ number_format($destination->planned_portion_count) }}
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-500">
                                    <div><span class="font-semibold text-slate-400">Tiba:</span> {{ $destination->arrived_at?->format('H:i') ?? '-' }}</div>
                                    <div><span class="font-semibold text-slate-400">Kirim:</span> {{ $destination->delivered_at?->format('H:i') ?? '-' }}</div>
                                </td>

                                <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-slate-700">
                                    {{ $destination->proof_notes ?: '-' }}
                                </td>
                            </tr>
                        @endforeach
                    </x-table>
                </x-card>
            </div>

            <!-- Timeline Status Column -->
            <div class="space-y-6">
                <x-card title="Timeline status (Kronologi Status)" subtitle="Lini masa pergerakan dan pembaruan logistik">
                    <div class="relative pl-6 space-y-6 before:absolute before:inset-0 before:left-2.5 before:w-0.5 before:bg-slate-200">
                        @foreach ($statusTimeline as $item)
                            <div class="relative flex items-start group">
                                <!-- Bullet icon -->
                                <div class="absolute -left-6 mt-1.5 w-5 h-5 rounded-full bg-white border-2 border-emerald-500 flex items-center justify-center shadow-xs">
                                    <div class="w-2 h-2 rounded-full bg-emerald-600"></div>
                                </div>
                                <div class="w-full bg-slate-50 p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                                    <div class="flex items-center justify-between gap-2 mb-1">
                                        <span class="text-xs font-bold text-slate-800">{{ $item['label'] }}</span>
                                        <span class="text-[10px] font-mono font-semibold text-slate-400 bg-white px-1.5 py-0.5 rounded border border-slate-200">{{ $item['time'] ?? '-' }}</span>
                                    </div>
                                    <div class="text-[11px] text-emerald-700 font-semibold uppercase tracking-wider">
                                        {{ str($item['status'])->replace('_', ' ')->title() }}
                                    </div>
                                    @if (!empty($item['notes']))
                                        <div class="mt-1.5 pt-1.5 border-t border-slate-200/60 text-xs text-slate-600 italic">
                                            "{{ $item['notes'] }}"
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </x-card>
            </div>

        </div>

    </div>
</x-layouts.app>
