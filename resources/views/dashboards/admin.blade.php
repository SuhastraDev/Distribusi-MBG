@php
    $officerCount = \App\Models\Officer::count();
    $locationCount = \App\Models\Location::count();
    $recipientCount = \App\Models\Recipient::count();
    $scheduleTodayCount = \App\Models\DistributionSchedule::whereDate('scheduled_date', today())->count();
@endphp

<x-layouts.app title="Dashboard Admin" breadcrumb="Menu Utama / Dashboard">
    <div x-data="{
        summary: null,
        loading: true,
        error: false,
        async init() {
            try {
                const response = await fetch('/api/frontend/dashboard-summary');
                if (!response.ok) throw new Error('Network response was not ok');
                this.summary = await response.json();
            } catch (e) {
                console.error(e);
                this.error = true;
            } finally {
                this.loading = false;
            }
        }
    }" class="space-y-8">

        <!-- Welcome Banner -->
        <div class="bg-gradient-to-r from-emerald-700 to-blue-700 rounded-2xl p-6 sm:p-8 text-white shadow-lg relative overflow-hidden">
            <div class="absolute -right-10 -bottom-10 w-64 h-64 bg-white/10 rounded-full blur-2xl pointer-events-none"></div>
            <div class="relative z-10 max-w-2xl">
                <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-emerald-900/40 border border-emerald-400/30 text-xs font-semibold text-emerald-200 mb-3">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Logistik & Pengawasan Realtime
                </div>
                <h2 class="text-2xl sm:text-3xl font-extrabold tracking-tight">Selamat Datang, {{ auth()->user()->name }}!</h2>
                <p class="mt-2 text-sm sm:text-base text-emerald-100/90 leading-relaxed">
                    Anda masuk sebagai <strong class="text-white font-semibold underline decoration-emerald-400 decoration-2 underline-offset-2">{{ auth()->user()->role?->display_name ?? 'Admin' }}</strong>. 
                    Kelola data master, pantau jadwal harian, dan jalankan kalkulasi rute distribusi makanan bergizi.
                </p>
            </div>
        </div>

        <!-- Quick Action Cards -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider px-1">Aksi Cepat</h3>
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <a href="{{ route('distribution-schedules.index') }}" 
                   class="flex items-center gap-4 p-4 rounded-xl bg-white border border-slate-200 hover:border-emerald-500 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-800 text-sm group-hover:text-emerald-700 transition-colors">Jadwal Distribusi</h4>
                        <p class="text-xs text-slate-500">Buat atau atur jadwal pengiriman</p>
                    </div>
                </a>

                <a href="{{ route('distribution-runs.index') }}" 
                   class="flex items-center gap-4 p-4 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-800 text-sm group-hover:text-blue-700 transition-colors">Generate Rute</h4>
                        <p class="text-xs text-slate-500">Kalkulasi rute terpendek (Greedy)</p>
                    </div>
                </a>

                <a href="{{ route('route-plans.index') }}" 
                   class="flex items-center gap-4 p-4 rounded-xl bg-white border border-slate-200 hover:border-blue-500 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition-colors">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-semibold text-slate-800 text-sm group-hover:text-blue-700 transition-colors">Monitoring Realtime</h4>
                        <p class="text-xs text-slate-500">Pantau peta & posisi armada</p>
                    </div>
                </a>
            </div>
        </div>

        <!-- Summary Cards Grid -->
        <div class="space-y-3">
            <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider px-1">Ringkasan Sistem</h3>
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-card padding="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Petugas Aktif</span>
                        <span class="p-2 rounded-lg bg-emerald-50 text-emerald-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg></span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ number_format($officerCount) }} <span class="text-xs font-normal text-slate-500">Personel</span></div>
                    <div class="mt-2 text-xs text-emerald-600 font-medium flex items-center gap-1">
                        <a href="{{ route('officers.index') }}" class="hover:underline">Kelola petugas &rarr;</a>
                    </div>
                </x-card>

                <x-card padding="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Lokasi & Depot</span>
                        <span class="p-2 rounded-lg bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg></span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ number_format($locationCount) }} <span class="text-xs font-normal text-slate-500">Titik Terdaftar</span></div>
                    <div class="mt-2 text-xs text-blue-600 font-medium flex items-center gap-1">
                        <a href="{{ route('locations.index') }}" class="hover:underline">Lihat lokasi &rarr;</a>
                    </div>
                </x-card>

                <x-card padding="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Penerima MBG</span>
                        <span class="p-2 rounded-lg bg-blue-50 text-blue-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg></span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ number_format($recipientCount) }} <span class="text-xs font-normal text-slate-500">Sekolah/Lembaga</span></div>
                    <div class="mt-2 text-xs text-blue-600 font-medium flex items-center gap-1">
                        <a href="{{ route('recipients.index') }}" class="hover:underline">Kelola penerima &rarr;</a>
                    </div>
                </x-card>

                <x-card padding="p-5">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Jadwal Hari Ini</span>
                        <span class="p-2 rounded-lg bg-amber-50 text-amber-600"><svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg></span>
                    </div>
                    <div class="mt-2 text-2xl font-extrabold text-slate-800">{{ number_format($scheduleTodayCount) }} <span class="text-xs font-normal text-slate-500">Jadwal</span></div>
                    <div class="mt-2 text-xs text-amber-600 font-medium flex items-center gap-1">
                        <a href="{{ route('distribution-schedules.index') }}" class="hover:underline">Jadwal hari ini &rarr;</a>
                    </div>
                </x-card>
            </div>
        </div>

        <!-- Realtime API Summary Metrics -->
        <div>
            <div class="flex items-center justify-between mb-3">
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-500">Status Operasional Distribusi</h3>
                <span class="text-xs text-slate-400 flex items-center gap-1">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span>
                    Live dari API Backend
                </span>
            </div>

            <!-- Skeleton Loader -->
            <div x-show="loading" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 animate-pulse">
                <div class="bg-white p-5 rounded-xl border border-slate-200 h-28"></div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 h-28"></div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 h-28"></div>
                <div class="bg-white p-5 rounded-xl border border-slate-200 h-28"></div>
            </div>

            <!-- Error State -->
            <div x-show="error" style="display: none;" class="p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
                Gagal memuat statistik dari server API. Silakan refresh halaman.
            </div>

            <!-- Loaded Stats -->
            <div x-show="!loading && !error" style="display: none;" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
                <x-card padding="p-5 border-l-4 border-l-blue-500">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Total Distribusi Run</div>
                    <div class="mt-2 text-3xl font-black text-slate-800" x-text="summary?.distributions?.total ?? 0">0</div>
                    <div class="mt-1 text-xs text-slate-500">Siap berangkat: <span class="font-semibold text-blue-600" x-text="summary?.distributions?.ready ?? 0">0</span></div>
                </x-card>

                <x-card padding="p-5 border-l-4 border-l-amber-500">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Distribusi Berjalan</div>
                    <div class="mt-2 text-3xl font-black text-amber-600" x-text="summary?.distributions?.in_progress ?? 0">0</div>
                    <div class="mt-1 text-xs text-slate-500">Sedang dikirim oleh armada</div>
                </x-card>

                <x-card padding="p-5 border-l-4 border-l-emerald-500">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Distribusi Selesai</div>
                    <div class="mt-2 text-3xl font-black text-emerald-600" x-text="summary?.distributions?.completed ?? 0">0</div>
                    <div class="mt-1 text-xs text-slate-500">Telah sampai ke tujuan</div>
                </x-card>

                <x-card padding="p-5 border-l-4 border-l-red-500">
                    <div class="text-xs font-semibold text-slate-400 uppercase tracking-wider">Terlambat / Dibatalkan</div>
                    <div class="mt-2 text-3xl font-black text-red-600" x-text="summary?.distributions?.cancelled ?? 0">0</div>
                    <div class="mt-1 text-xs text-slate-500">Perlu tindak lanjut atau evaluasi</div>
                </x-card>
            </div>
        </div>

        <!-- Latest Distributions Table -->
        <x-card title="Distribusi Aktual Terbaru" subtitle="Daftar pengiriman terakhir yang tercatat dalam sistem">
            <x-slot name="headerAction">
                <x-button variant="outline" size="sm" href="{{ route('distribution-runs.index') }}">
                    Lihat Semua &rarr;
                </x-button>
            </x-slot>

            <div x-show="loading" class="py-12 text-center text-sm text-slate-400">
                Memuat data distribusi terbaru...
            </div>

            <div x-show="!loading && (!summary?.latest_distributions || summary?.latest_distributions.length === 0)" style="display: none;">
                <x-empty-state title="Belum Ada Distribusi Berjalan" description="Jadwal distribusi baru akan muncul di sini setelah diproses menjadi run." />
            </div>

            <div x-show="!loading && summary?.latest_distributions?.length > 0" style="display: none;">
                <x-table :headers="['Kode Run', 'Depot Asal', 'Petugas', 'Tanggal', 'Status', 'Aksi']">
                    <template x-for="run in summary?.latest_distributions" :key="run.id">
                        <tr class="hover:bg-slate-50/80 transition-colors">
                            <td class="px-4 py-3 font-semibold text-slate-800" x-text="run.code"></td>
                            <td class="px-4 py-3" x-text="run.depot?.name ?? '-'"></td>
                            <td class="px-4 py-3" x-text="run.officer?.name ?? '-'"></td>
                            <td class="px-4 py-3 text-slate-500" x-text="run.scheduled_date"></td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border"
                                      :class="{
                                          'bg-emerald-50 text-emerald-700 border-emerald-200': run.status === 'completed',
                                          'bg-amber-50 text-amber-700 border-amber-200': run.status === 'in_progress',
                                          'bg-blue-50 text-blue-700 border-blue-200': run.status === 'ready',
                                          'bg-red-50 text-red-700 border-red-200': run.status === 'cancelled'
                                      }"
                                      x-text="run.status.toUpperCase()"></span>
                            </td>
                            <td class="px-4 py-3">
                                <a :href="`/distribution-runs/${run.id}`" class="text-emerald-600 hover:text-emerald-800 font-medium text-xs hover:underline">Detail &rarr;</a>
                            </td>
                        </tr>
                    </template>
                </x-table>
            </div>
        </x-card>
    </div>
</x-layouts.app>
