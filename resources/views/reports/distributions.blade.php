<x-layouts.app title="Laporan & Rekapitulasi Distribusi" breadcrumb="Laporan / Distribusi">
    <div class="space-y-8">
        
        <!-- Header & Action Panel -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Rekapitulasi Kinerja Logistik MBG</h2>
                <p class="text-sm text-slate-500">Analisis komprehensif status pengiriman, realisasi porsi, dan jarak tempuh armada</p>
            </div>
            <div class="flex flex-wrap items-center gap-2.5 shrink-0">
                <a href="{{ route('reports.distributions.export', request()->query()) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-white hover:bg-slate-50 text-slate-700 border border-slate-300 shadow-2xs transition-all">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export CSV
                </a>
                <a href="{{ route('reports.distributions.export-excel', request()->query()) }}" 
                   class="inline-flex items-center gap-2 px-4 py-2 rounded-xl text-xs font-bold bg-emerald-600 hover:bg-emerald-700 text-white shadow-md transition-all">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Export Excel (.xls)
                </a>
            </div>
        </div>

        <!-- Summary Metric Cards Grid -->
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Run <span class="text-slate-400 font-normal lowercase">(Total distribusi: {{ $summary['total_runs'] }})</span></div>
                <div class="mt-1 text-2xl font-black text-slate-800">{{ number_format($summary['total_runs']) }} <span class="text-xs font-normal text-slate-400">tugas</span></div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Selesai <span class="text-slate-400 font-normal lowercase">(Selesai: {{ $summary['completed_runs'] }})</span></div>
                <div class="mt-1 text-2xl font-black text-emerald-600">{{ number_format($summary['completed_runs']) }} <span class="text-xs font-normal text-slate-400">run</span></div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Sedang Berjalan <span class="text-slate-400 font-normal lowercase">(Berjalan: {{ $summary['in_progress_runs'] }})</span></div>
                <div class="mt-1 text-2xl font-black text-blue-600">{{ number_format($summary['in_progress_runs']) }} <span class="text-xs font-normal text-slate-400">run</span></div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold text-amber-600 uppercase tracking-wider">Siap Kirim</div>
                <div class="mt-1 text-2xl font-black text-amber-600">{{ number_format($summary['ready_runs']) }} <span class="text-xs font-normal text-slate-400">run</span></div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold text-emerald-600 uppercase tracking-wider">Realisasi Porsi</div>
                <div class="mt-1 text-xl font-black text-emerald-900">{{ number_format($summary['delivered_portions']) }} <span class="text-xs font-normal text-slate-400">/ {{ number_format($summary['planned_portions']) }}</span></div>
            </div>

            <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs">
                <div class="text-[11px] font-bold text-blue-600 uppercase tracking-wider">Total Jarak</div>
                <div class="mt-1 text-xl font-black text-blue-700">{{ number_format($summary['total_distance_km'], 1) }} <span class="text-xs font-normal text-slate-400">km</span></div>
            </div>
        </div>

        <!-- Filter Card -->
        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-2xs">
            <form method="GET" action="{{ route('reports.distributions.index') }}" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 items-end">
                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Dari Tanggal:</label>
                    <input type="date" name="date_from" value="{{ $filters['date_from'] ?? '' }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Sampai Tanggal:</label>
                    <input type="date" name="date_to" value="{{ $filters['date_to'] ?? '' }}" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-medium">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Petugas Lapangan:</label>
                    <select name="officer_id" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-medium bg-white">
                        <option value="">Semua Petugas</option>
                        @foreach ($officers as $officer)
                            <option value="{{ $officer->id }}" @selected((string) ($filters['officer_id'] ?? '') === (string) $officer->id)>
                                {{ $officer->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-700 mb-1">Status Run:</label>
                    <select name="status" class="w-full px-3 py-2 text-xs rounded-xl border border-slate-200 focus:ring-2 focus:ring-emerald-500 font-medium bg-white">
                        <option value="">Semua Status</option>
                        @foreach (['ready', 'in_progress', 'completed', 'cancelled'] as $status)
                            <option value="{{ $status }}" @selected(($filters['status'] ?? '') === $status)>
                                {{ str($status)->replace('_', ' ')->title() }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-2">
                    <button type="submit" class="w-full py-2 px-4 rounded-xl bg-slate-800 hover:bg-slate-900 text-white text-xs font-bold shadow-2xs transition-all cursor-pointer">
                        Filter Data
                    </button>
                    <a href="{{ route('reports.distributions.index') }}" class="py-2 px-3 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-600 text-xs font-bold transition-all text-center">
                        Reset
                    </a>
                </div>
            </form>
        </div>

        <!-- Table Card -->
        <x-card padding="p-0">
            @if ($distributionRuns->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Tidak Ada Data Laporan" 
                        description="Belum ada catatan distribusi yang sesuai dengan kriteria pencarian atau filter yang Anda pilih."
                    />
                </div>
            @else
                <x-table :headers="['Kode Run', 'Tanggal', 'Petugas', 'Depot Asal', 'Status', 'Tujuan (Terkirim / Total)', 'Porsi (Terkirim / Rencana)', 'Jarak Rute', 'Aksi']">
                    @foreach ($distributionRuns as $run)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-800 font-mono text-xs">
                                <span class="bg-slate-100 px-2 py-1 rounded border border-slate-200">{{ $run->code }}</span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-medium text-slate-700">
                                {{ $run->schedule->scheduled_date->format('d/m/Y') }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-bold text-slate-700">
                                {{ $run->officer->name }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                {{ $run->schedule->depot->name }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $badgeVar = match($run->status) {
                                        'ready' => 'default',
                                        'in_progress' => 'active',
                                        'completed' => 'active',
                                        'cancelled' => 'inactive',
                                        default => 'default',
                                    };
                                    $statusName = match($run->status) {
                                        'ready' => 'Siap Kirim',
                                        'in_progress' => 'Sedang Jalan',
                                        'completed' => 'Selesai Terkirim',
                                        'cancelled' => 'Dibatalkan',
                                        default => str($run->status)->replace('_', ' ')->title(),
                                    };
                                @endphp
                                <x-badge :variant="$badgeVar">
                                    {{ $statusName }}
                                </x-badge>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold text-slate-700">
                                <span class="text-emerald-600 font-bold">{{ $run->destinations->where('status', 'delivered')->count() }}</span> / {{ $run->destinations->count() }} Sekolah
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-semibold text-slate-700">
                                <span class="text-indigo-600 font-bold">{{ number_format($run->destinations->where('status', 'delivered')->sum('delivered_portion_count')) }}</span> / {{ number_format($run->destinations->sum('planned_portion_count')) }} Porsi
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-bold text-slate-700">
                                {{ number_format($run->routePlan?->total_distance_km ?? 0, 1) }} km
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-xs font-bold space-x-1.5">
                                <a href="{{ route('reports.distributions.show', $run) }}" class="inline-block px-2.5 py-1 rounded bg-indigo-50 text-indigo-700 hover:bg-indigo-600 hover:text-white transition-colors border border-indigo-100">
                                    Laporan
                                </a>
                                <a href="{{ route('distribution-runs.show', $run) }}" class="inline-block px-2.5 py-1 rounded bg-slate-100 text-slate-700 hover:bg-emerald-600 hover:text-white transition-colors border border-slate-200">
                                    Monitoring
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $distributionRuns->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
