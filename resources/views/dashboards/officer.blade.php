@php
    $user = auth()->user();
    $officer = $user->officer;
    $runsToday = $officer 
        ? \App\Models\DistributionRun::where('officer_id', $officer->id)
            ->whereHas('schedule', fn($q) => $q->whereDate('scheduled_date', today()))
            ->with(['schedule.depot', 'routePlan', 'destinations.recipient', 'destinations.location'])
            ->get() 
        : collect();

    $activeRun = $runsToday->where('status', 'in_progress')->first() ?? $runsToday->where('status', 'ready')->first();
    $completedCount = $runsToday->where('status', 'completed')->count();
@endphp

<x-layouts.app title="Dashboard Petugas Distribusi" breadcrumb="Menu Petugas / Dashboard">
    <div x-data="{
        runs: @js($runsToday),
        loading: false,
        async refreshData() {
            this.loading = true;
            try {
                const res = await fetch('/api/frontend/distribution-runs');
                if (res.ok) {
                    const json = await res.json();
                    // Live check from API
                }
            } catch(e) { console.error(e); }
            finally { this.loading = false; }
        }
    }" class="space-y-8">

        <!-- Officer Banner -->
        <div class="bg-gradient-to-r from-slate-900 via-emerald-950 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg border border-slate-800">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 border border-emerald-500/30 text-xs font-semibold text-emerald-300 mb-3">
                        <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                        Armada Logistik MBG &bull; {{ $officer?->code ?? 'PTG' }}
                    </div>
                    <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Halo, {{ $user->name }}!</h2>
                    <p class="mt-1 text-sm text-slate-300">
                        Siap menjalankan amanah pengiriman paket makanan bergizi gratis hari ini.
                    </p>
                </div>
                <div class="shrink-0 flex items-center gap-3">
                    <button @click="refreshData()" class="px-4 py-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-sm font-medium text-slate-200 border border-slate-700 transition-colors flex items-center gap-2">
                        <svg :class="{ 'animate-spin': loading }" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path></svg>
                        Refresh Live Data
                    </button>
                </div>
            </div>
        </div>

        <!-- Today Summary Metric Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            <x-card padding="p-5 border-l-4 border-l-blue-500">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Tugas Hari Ini</div>
                <div class="mt-2 text-3xl font-black text-slate-800">{{ $runsToday->count() }} <span class="text-xs font-normal text-slate-500">Jadwal</span></div>
                <div class="mt-1 text-xs text-slate-500">Tanggal: {{ today()->format('d M Y') }}</div>
            </x-card>

            <x-card padding="p-5 border-l-4 border-l-amber-500">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Sedang Berjalan / Aktif</div>
                <div class="mt-2 text-3xl font-black text-amber-600">{{ $runsToday->whereIn('status', ['ready', 'in_progress'])->count() }}</div>
                <div class="mt-1 text-xs text-slate-500">Siap atau sedang dalam perjalanan</div>
            </x-card>

            <x-card padding="p-5 border-l-4 border-l-emerald-500">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Tugas Selesai</div>
                <div class="mt-2 text-3xl font-black text-emerald-600">{{ $completedCount }}</div>
                <div class="mt-1 text-xs text-slate-500">Telah terkirim sempurna</div>
            </x-card>
        </div>

        <!-- Active Distribution Card -->
        @if ($activeRun)
            <div class="bg-gradient-to-br from-emerald-900 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-xl border border-emerald-500/30">
                <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
                    <div class="space-y-3">
                        <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-500/30 text-xs font-bold uppercase tracking-wider">
                            <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
                            Distribusi Aktif Saat Ini
                        </div>
                        <h3 class="text-2xl font-bold tracking-tight">Run: {{ $activeRun->code }}</h3>
                        <p class="text-sm text-slate-300 max-w-xl">
                            Depot Asal: <strong class="text-white">{{ $activeRun->schedule->depot->name ?? 'Depot Utama' }}</strong> &bull; 
                            Total Tujuan: <strong class="text-white">{{ $activeRun->destinations->count() }} Titik Sekolah</strong>
                        </p>
                        @if ($activeRun->routePlan)
                            <div class="flex items-center gap-4 text-xs text-emerald-300 font-medium pt-1">
                                <span>Total Jarak: <strong>{{ (float) $activeRun->routePlan->total_distance_km }} km</strong></span>
                                <span>&bull;</span>
                                <span>Estimasi Waktu: <strong>{{ $activeRun->routePlan->total_estimated_minutes }} menit</strong></span>
                            </div>
                        @endif
                    </div>

                    <div class="flex flex-wrap items-center gap-3 shrink-0">
                        @if ($activeRun->routePlan)
                            <a href="{{ route('route-plans.show', $activeRun->routePlan) }}" 
                               class="inline-flex items-center gap-2 px-5 py-3 rounded-xl bg-slate-800 hover:bg-slate-700 text-white font-semibold text-sm border border-slate-700 shadow-md transition-all">
                                <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                                Lihat Rute di Peta
                            </a>
                        @endif

                        <a href="{{ route('distribution-runs.show', $activeRun) }}" 
                           class="inline-flex items-center gap-2 px-6 py-3 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white font-semibold text-sm shadow-lg shadow-emerald-900/50 transition-all">
                            <span>Update Status Pengiriman</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </div>
                </div>
            </div>
        @else
            <!-- Empty State jika belum ada jadwal -->
            <x-card>
                <x-empty-state 
                    title="Belum Ada Distribusi Aktif" 
                    description="Anda tidak memiliki jadwal pengiriman yang sedang berjalan saat ini. Silakan periksa daftar tugas secara berkala."
                >
                    <x-button variant="outline" size="sm" href="{{ route('distribution-runs.index') }}" class="mt-2">
                        Lihat Daftar Tugas Saya &rarr;
                    </x-button>
                </x-empty-state>
            </x-card>
        @endif

        <!-- List Jadwal Hari Ini -->
        <x-card title="Daftar Jadwal Distribusi Anda Hari Ini" subtitle="Semua tugas pengiriman yang ditugaskan kepada Anda">
            @if ($runsToday->isEmpty())
                <x-empty-state 
                    title="Jadwal Hari Ini Kosong" 
                    description="Belum ada jadwal distribusi yang ditugaskan kepada akun Anda pada hari ini." 
                />
            @else
                <x-table :headers="['Kode Run', 'Depot', 'Jumlah Sekolah', 'Status', 'Aksi']">
                    @foreach ($runsToday as $run)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $run->code }}</td>
                            <td class="px-4 py-3">{{ $run->schedule->depot->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $run->destinations->count() }} Sekolah</td>
                            <td class="px-4 py-3">
                                <x-badge :variant="$run->status">{{ strtoupper($run->status) }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('distribution-runs.show', $run) }}" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs hover:underline">Buka Tugas &rarr;</a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

    </div>
</x-layouts.app>
