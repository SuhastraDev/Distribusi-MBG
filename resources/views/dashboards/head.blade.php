@php
    $todayRuns = \App\Models\DistributionRun::whereHas('schedule', fn($q) => $q->whereDate('scheduled_date', today()))
        ->with(['schedule.depot', 'officer', 'destinations'])
        ->get();

    $totalToday = $todayRuns->count();
    $completedToday = $todayRuns->where('status', 'completed')->count();
    $percentCompleted = $totalToday > 0 ? round(($completedToday / $totalToday) * 100) : 0;
    $lateToday = $todayRuns->where('status', 'cancelled')->count();
    $activeRuns = $todayRuns->whereIn('status', ['ready', 'in_progress']);
@endphp

<x-layouts.app title="Dashboard Kepala SPPG" breadcrumb="Menu Utama / Pengawasan">
    <div x-data="{
        reportSummary: null,
        loading: true,
        async init() {
            try {
                const res = await fetch('/api/frontend/reports/distributions/summary?date_from={{ today()->format('Y-m-d') }}&date_to={{ today()->format('Y-m-d') }}');
                if (res.ok) {
                    this.reportSummary = await res.json();
                }
            } catch(e) { console.error(e); }
            finally { this.loading = false; }
        }
    }" class="space-y-8">

        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-blue-900 via-indigo-900 to-slate-900 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden border border-blue-500/30">
            <div class="absolute right-0 bottom-0 translate-x-1/4 translate-y-1/4 w-80 h-80 bg-blue-500/10 rounded-full blur-3xl pointer-events-none"></div>
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-blue-500/20 border border-blue-400/30 text-xs font-semibold text-blue-200 mb-3">
                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-pulse"></span>
                    Eksekutif & Pengawasan SPPG
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ auth()->user()->name }}</h2>
                <p class="mt-2 text-sm sm:text-base text-blue-100/90 leading-relaxed">
                    Pantau kinerja distribusi makanan bergizi gratis secara realtime, periksa persentase ketercapaian pengiriman hari ini, dan unduh laporan eksekutif.
                </p>
            </div>
        </div>

        <!-- Metrics Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            <x-card padding="p-5 border-l-4 border-l-blue-500">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Distribusi Hari Ini</div>
                <div class="mt-2 text-3xl font-black text-slate-800">{{ $totalToday }} <span class="text-xs font-normal text-slate-500">Run</span></div>
                <div class="mt-1 text-xs text-slate-500">Tanggal: {{ today()->format('d M Y') }}</div>
            </x-card>

            <x-card padding="p-5 border-l-4 border-l-emerald-500">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Persentase Selesai</div>
                <div class="mt-2 text-3xl font-black text-emerald-600">{{ $percentCompleted }}%</div>
                <div class="mt-1 text-xs text-slate-500">{{ $completedToday }} dari {{ $totalToday }} pengiriman berhasil</div>
            </x-card>

            <x-card padding="p-5 border-l-4 border-l-red-500">
                <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Terlambat / Dibatalkan</div>
                <div class="mt-2 text-3xl font-black text-red-600">{{ $lateToday }}</div>
                <div class="mt-1 text-xs text-slate-500">Memerlukan perhatian atau evaluasi</div>
            </x-card>
        </div>

        <!-- Quick Links & Monitoring Preview Panel -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <!-- Monitoring Preview Card -->
            <div class="bg-gradient-to-br from-slate-900 to-slate-800 rounded-2xl p-6 text-white border border-slate-700 shadow-lg flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-emerald-400">Peta & Pengawasan Live</span>
                        <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-full text-[11px] font-semibold bg-emerald-500/20 text-emerald-300 border border-emerald-500/30">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-ping"></span> Live GPS
                        </span>
                    </div>
                    <h3 class="text-xl font-bold">Monitoring Realtime Armada</h3>
                    <p class="text-sm text-slate-300 mt-2">
                        Pantau pergerakan armada petugas, posisi GPS terakhir, dan urutan titik rute sekolah penerima makanan secara langsung pada peta interaktif.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-700/80 flex items-center justify-between">
                    <span class="text-xs text-slate-400">{{ $activeRuns->count() }} armada sedang beroperasi</span>
                    <a href="{{ route('route-plans.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-600 hover:bg-emerald-500 text-white text-xs font-semibold shadow-md transition-colors">
                        Buka Peta Monitoring &rarr;
                    </a>
                </div>
            </div>

            <!-- Report Quick Link Card -->
            <div class="bg-white rounded-2xl p-6 border border-slate-200 shadow-sm flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-bold uppercase tracking-wider text-blue-600">Rekapitulasi & Eksekutif</span>
                        <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    </div>
                    <h3 class="text-xl font-bold text-slate-800">Laporan Distribusi Pangan</h3>
                    <p class="text-sm text-slate-500 mt-2">
                        Akses rekapitulasi lengkap porsi terkirim, efisiensi waktu, total jarak tempuh, dan ekspor laporan ke dalam format PDF atau Excel.
                    </p>
                </div>
                <div class="mt-6 pt-4 border-t border-slate-100 flex items-center justify-between">
                    <span class="text-xs text-slate-400">Data harian & bulanan siap diunduh</span>
                    <a href="{{ route('reports.distributions.index') }}" class="inline-flex items-center gap-2 px-4 py-2 rounded-xl bg-blue-600 hover:bg-blue-700 text-white text-xs font-semibold shadow-sm transition-colors">
                        Lihat Laporan &rarr;
                    </a>
                </div>
            </div>
        </div>

        <!-- Ringkasan Distribusi Aktif Hari Ini -->
        <x-card title="Distribusi Aktif Hari Ini" subtitle="Daftar pengiriman yang sedang diproses oleh petugas di lapangan">
            @if ($activeRuns->isEmpty())
                <x-empty-state 
                    title="Tidak Ada Distribusi Aktif" 
                    description="Semua distribusi hari ini telah selesai atau belum ada jadwal yang siap dijalankan." 
                />
            @else
                <x-table :headers="['Kode Run', 'Depot', 'Petugas', 'Sekolah Tujuan', 'Status', 'Aksi']">
                    @foreach ($activeRuns as $run)
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800">{{ $run->code }}</td>
                            <td class="px-4 py-3">{{ $run->schedule->depot->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $run->officer->name ?? '-' }}</td>
                            <td class="px-4 py-3">{{ $run->destinations->count() }} Titik</td>
                            <td class="px-4 py-3">
                                <x-badge :variant="$run->status">{{ strtoupper($run->status) }}</x-badge>
                            </td>
                            <td class="px-4 py-3">
                                <a href="{{ route('distribution-runs.show', $run) }}" class="text-blue-600 hover:text-blue-800 font-medium text-xs hover:underline">Pantau &rarr;</a>
                            </td>
                        </tr>
                    @endforeach
                </x-table>
            @endif
        </x-card>

    </div>
</x-layouts.app>
