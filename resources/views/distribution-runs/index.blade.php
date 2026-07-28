<x-layouts.app title="Data Distribusi Aktual (Run)" breadcrumb="Operasional / Distribusi Aktual">
    <div x-data="{
        search: '',
        dateFilter: '',
        officerFilter: 'all',
        statusFilter: 'all',
        matchRow(code, scheduleCode, officerId, dateStr, status) {
            const matchesSearch = this.search === '' || 
                code.toLowerCase().includes(this.search.toLowerCase()) || 
                scheduleCode.toLowerCase().includes(this.search.toLowerCase());
            
            const matchesDate = this.dateFilter === '' || dateStr === this.dateFilter;
            const matchesOfficer = this.officerFilter === 'all' || officerId.toString() === this.officerFilter;
            const matchesStatus = this.statusFilter === 'all' || status === this.statusFilter;

            return matchesSearch && matchesDate && matchesOfficer && matchesStatus;
        }
    }" class="space-y-6">

        <!-- Top Header Panel -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Distribusi Aktual di Lapangan (Run)</h2>
                <p class="text-sm text-slate-500">Pantau eksekusi pengiriman paket masakan harian yang dilakukan oleh petugas ke sekolah tujuan</p>
            </div>
            @if (in_array(auth()->user()->role->name ?? '', ['admin', 'petugas'], true))
                <div class="shrink-0">
                    <x-button variant="primary" href="{{ route('distribution-runs.create') }}" class="w-full sm:w-auto">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                        Buat Distribusi dari Jadwal
                    </x-button>
                </div>
            @endif
        </div>

        <!-- Filter & Search Bar -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-64">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari kode run atau jadwal..." 
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors bg-slate-50/50">
            </div>

            <div class="flex flex-wrap items-center gap-3 w-full md:w-auto">
                <!-- Filter Tanggal -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-semibold text-slate-500 shrink-0">Tanggal:</label>
                    <input type="date" x-model="dateFilter" class="w-full sm:w-auto px-3 py-1.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                    <button x-show="dateFilter !== ''" @click="dateFilter = ''" type="button" class="text-xs text-red-500 hover:underline">Reset</button>
                </div>

                <!-- Filter Petugas -->
                @php
                    $allOfficers = $distributionRuns->pluck('officer')->filter()->unique('id');
                @endphp
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-semibold text-slate-500 shrink-0">Petugas:</label>
                    <select x-model="officerFilter" class="w-full sm:w-auto px-3 py-1.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                        <option value="all">Semua Petugas</option>
                        @foreach ($allOfficers as $off)
                            <option value="{{ $off->id }}">{{ $off->name }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Filter Status -->
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-semibold text-slate-500 shrink-0">Status:</label>
                    <select x-model="statusFilter" class="w-full sm:w-auto px-3 py-1.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                        <option value="all">Semua Status</option>
                        <option value="ready">Ready (Siap Kirim)</option>
                        <option value="in_progress">In Progress (Dalam Perjalanan)</option>
                        <option value="completed">Completed (Selesai)</option>
                        <option value="cancelled">Cancelled (Batal)</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Table Card -->
        <x-card padding="p-0">
            @if ($distributionRuns->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Belum Ada Distribusi Aktual" 
                        description="Belum ada proses distribusi run yang dibuat atau dijadwalkan. Klik tombol di atas untuk membuat distribusi baru dari jadwal yang sudah ada."
                    >
                        @if (in_array(auth()->user()->role->name ?? '', ['admin', 'petugas'], true))
                            <x-button variant="primary" size="sm" href="{{ route('distribution-runs.create') }}" class="mt-2">
                                Buat Distribusi dari Jadwal
                            </x-button>
                        @endif
                    </x-empty-state>
                </div>
            @else
                <x-table :headers="['Kode Run', 'Jadwal Asal', 'Petugas Lapangan', 'Status Distribusi', 'Waktu Mulai', 'Waktu Selesai', 'Aksi']">
                    @foreach ($distributionRuns as $run)
                        <tr x-show="matchRow('{{ addslashes($run->code) }}', '{{ addslashes($run->schedule->code ?? '') }}', '{{ $run->officer_id }}', '{{ $run->schedule->scheduled_date ? $run->schedule->scheduled_date->format('Y-m-d') : '' }}', '{{ $run->status }}')" 
                            class="hover:bg-slate-50/80 transition-colors">
                            
                            <!-- Kode Run -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 bg-indigo-50 text-indigo-900 px-2.5 py-1 rounded-md text-xs border border-indigo-200 font-mono">
                                    {{ $run->code }}
                                </span>
                            </td>

                            <!-- Jadwal Asal -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-bold text-slate-700 text-xs font-mono">{{ $run->schedule->code ?? '-' }}</div>
                                <div class="text-[11px] text-slate-500">{{ $run->schedule->scheduled_date?->format('d M Y') ?? '-' }}</div>
                            </td>

                            <!-- Petugas -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2">
                                    <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-[10px] shrink-0">
                                        {{ substr($run->officer->name ?? '?', 0, 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-slate-700">{{ $run->officer->name ?? 'Petugas Terhapus' }}</div>
                                </div>
                            </td>

                            <!-- Status -->
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @php
                                    $badgeVariant = match($run->status) {
                                        'ready' => 'default',
                                        'in_progress' => 'active',
                                        'completed' => 'active',
                                        'cancelled' => 'inactive',
                                        default => 'default',
                                    };
                                    $statusLabel = match($run->status) {
                                        'ready' => 'Siap Kirim (Ready)',
                                        'in_progress' => 'Sedang Jalan (In Progress)',
                                        'completed' => 'Selesai Terkirim',
                                        'cancelled' => 'Dibatalkan',
                                        default => str($run->status)->replace('_', ' ')->title(),
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold 
                                    @if($run->status === 'in_progress') bg-blue-100 text-blue-800 border border-blue-200 animate-pulse
                                    @elseif($run->status === 'completed') bg-emerald-100 text-emerald-800 border border-emerald-200
                                    @elseif($run->status === 'cancelled') bg-red-100 text-red-800 border border-red-200
                                    @else bg-amber-100 text-amber-800 border border-amber-200 @endif">
                                    {{ $statusLabel }}
                                </span>
                            </td>

                            <!-- Mulai -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                @if ($run->started_at)
                                    <div class="font-semibold text-slate-800">{{ $run->started_at->format('H:i') }} WIB</div>
                                    <div class="text-[10px] text-slate-400">{{ $run->started_at->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400 italic">Belum dimulai</span>
                                @endif
                            </td>

                            <!-- Selesai -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-xs text-slate-600">
                                @if ($run->completed_at)
                                    <div class="font-semibold text-emerald-700">{{ $run->completed_at->format('H:i') }} WIB</div>
                                    <div class="text-[10px] text-slate-400">{{ $run->completed_at->format('d/m/Y') }}</div>
                                @else
                                    <span class="text-slate-400 italic">Belum selesai</span>
                                @endif
                            </td>

                            <!-- Aksi -->
                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <a href="{{ route('distribution-runs.show', $run) }}" 
                                   class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-xs font-semibold bg-slate-100 hover:bg-emerald-600 text-slate-700 hover:text-white transition-all shadow-2xs">
                                    <span>Detail & Monitoring</span>
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
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
