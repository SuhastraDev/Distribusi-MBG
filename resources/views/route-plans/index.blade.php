<x-layouts.app title="Monitoring Rute Distribusi (Greedy Algorithm)" breadcrumb="Operasional / Monitoring Rute">
    <div x-data="{
        search: '',
        matchRow(code, runCode, officerName) {
            return this.search === '' || 
                code.toLowerCase().includes(this.search.toLowerCase()) || 
                runCode.toLowerCase().includes(this.search.toLowerCase()) ||
                officerName.toLowerCase().includes(this.search.toLowerCase());
        }
    }" class="space-y-6">

        <!-- Header Panel -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Rute Greedy Distribusi</h2>
                <p class="text-sm text-slate-500">Hasil optimasi urutan titik pengiriman menggunakan algoritma Greedy Nearest Neighbor</p>
            </div>
            <div class="shrink-0">
                <x-button variant="outline" href="{{ route('distribution-runs.index') }}">
                    Lihat Data Distribusi (Run) &rarr;
                </x-button>
            </div>
        </div>

        <!-- Search Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex items-center justify-between">
            <div class="relative w-full md:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari kode rute, kode run, atau nama petugas..." 
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 bg-slate-50/50">
            </div>
            <div class="text-xs text-slate-400 hidden sm:block">
                Total <span class="font-bold text-slate-700">{{ $routePlans->total() }}</span> rute tercatat
            </div>
        </div>

        <!-- Table Card -->
        <x-card padding="p-0">
            @if ($routePlans->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Belum Ada Rute Distribusi" 
                        description="Rute pengiriman belum digenerate dari data distribusi (Run). Anda dapat men-generate rute melalui halaman Detail Distribusi Aktual."
                    >
                        <x-button variant="primary" size="sm" href="{{ route('distribution-runs.index') }}" class="mt-2">
                            Buka Daftar Distribusi
                        </x-button>
                    </x-empty-state>
                </div>
            @else
                <x-table :headers="['Kode Rute', 'Distribusi (Run)', 'Petugas Lapangan', 'Algoritma Optimasi', 'Total Jarak', 'Estimasi Waktu', 'Aksi']">
                    @foreach ($routePlans as $routePlan)
                        <tr x-show="matchRow('{{ addslashes($routePlan->code) }}', '{{ addslashes($routePlan->run->code ?? '') }}', '{{ addslashes($routePlan->run->officer->name ?? '') }}')" 
                            class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- Kode Rute -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 bg-indigo-50 text-indigo-900 px-2.5 py-1 rounded-md text-xs border border-indigo-200 font-mono">
                                    {{ $routePlan->code }}
                                </span>
                            </td>

                            <!-- Distribusi Run -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-700 text-sm font-mono">
                                    <a href="{{ route('distribution-runs.show', $routePlan->distribution_run_id) }}" class="hover:text-emerald-600 hover:underline">
                                        {{ $routePlan->run->code ?? '-' }}
                                    </a>
                                </div>
                                <div class="text-[11px] text-slate-400">{{ $routePlan->run->schedule->scheduled_date?->format('d M Y') ?? '-' }}</div>
                            </td>

                            <!-- Petugas -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-[10px] shrink-0">
                                        {{ substr($routePlan->run->officer->name ?? '?', 0, 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-slate-700">{{ $routePlan->run->officer->name ?? 'Petugas Terhapus' }}</div>
                                </div>
                            </td>

                            <!-- Algoritma -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-slate-100 text-slate-700 border border-slate-200">
                                    <svg class="w-3.5 h-3.5 text-indigo-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                                    {{ str($routePlan->algorithm)->replace('_', ' ')->title() }}
                                </span>
                            </td>

                            <!-- Jarak -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-bold text-slate-800 text-sm">
                                {{ number_format($routePlan->total_distance_km, 1) }} <span class="text-xs font-normal text-slate-400">km</span>
                            </td>

                            <!-- Estimasi -->
                            <td class="px-4 py-3.5 whitespace-nowrap font-bold text-emerald-700 text-sm">
                                {{ $routePlan->total_estimated_minutes }} <span class="text-xs font-normal text-slate-400">menit</span>
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('route-plans.show', $routePlan) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-emerald-50 hover:bg-emerald-600 text-emerald-700 hover:text-white transition-all shadow-2xs border border-emerald-200 hover:border-emerald-600">
                                    <span>Lihat Peta & Urutan</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $routePlans->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
