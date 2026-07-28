<x-layouts.app title="Jadwal Distribusi MBG" breadcrumb="Operasional / Jadwal Distribusi">
    <div x-data="{
        search: '',
        dateFilter: '',
        statusFilter: 'all',
        matchRow(code, officer, depot, dateStr, status) {
            const matchesSearch = this.search === '' || 
                code.toLowerCase().includes(this.search.toLowerCase()) || 
                officer.toLowerCase().includes(this.search.toLowerCase()) ||
                depot.toLowerCase().includes(this.search.toLowerCase());
            
            const matchesDate = this.dateFilter === '' || dateStr === this.dateFilter;
            const matchesStatus = this.statusFilter === 'all' || status === this.statusFilter;

            return matchesSearch && matchesDate && matchesStatus;
        }
    }" class="space-y-6">

        <!-- Top Panel & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Rencana & Jadwal Distribusi</h2>
                <p class="text-sm text-slate-500">Kelola jadwal pengiriman harian dari depot ke sekolah tujuan sebelum pembuatan rute</p>
            </div>
            <div class="shrink-0">
                <x-button variant="primary" href="{{ route('distribution-schedules.create') }}" class="w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Buat Jadwal Baru
                </x-button>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col md:flex-row gap-4 items-center justify-between">
            <div class="relative w-full md:w-72">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari kode, petugas, atau depot..." 
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors bg-slate-50/50">
            </div>

            <div class="flex flex-col sm:flex-row items-center gap-3 w-full md:w-auto">
                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-semibold text-slate-500 shrink-0">Tanggal:</label>
                    <input type="date" x-model="dateFilter" class="w-full sm:w-auto px-3 py-1.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                    <button x-show="dateFilter !== ''" @click="dateFilter = ''" type="button" class="text-xs text-red-500 hover:underline">Reset</button>
                </div>

                <div class="flex items-center gap-2 w-full sm:w-auto">
                    <label class="text-xs font-semibold text-slate-500 shrink-0">Status:</label>
                    <select x-model="statusFilter" class="w-full sm:w-auto px-3 py-1.5 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                        <option value="all">Semua Status</option>
                        <option value="draft">Draft</option>
                        <option value="scheduled">Terjadwal</option>
                        <option value="cancelled">Dibatalkan</option>
                    </select>
                </div>
            </div>
        </div>

        <!-- Schedules Table -->
        <x-card padding="p-0">
            @if ($distributionSchedules->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Belum Ada Jadwal Distribusi" 
                        description="Jadwal pengiriman dari depot masakan ke sekolah penerima belum dibuat. Klik tombol di atas untuk merencanakan jadwal baru."
                    >
                        <x-button variant="primary" size="sm" href="{{ route('distribution-schedules.create') }}" class="mt-2">
                            Buat Jadwal Baru
                        </x-button>
                    </x-empty-state>
                </div>
            @else
                <x-table :headers="['Kode Jadwal', 'Tanggal Distribusi', 'Petugas Lapangan', 'Depot Asal', 'Total Porsi', 'Status Rute', 'Status Jadwal', 'Aksi']">
                    @foreach ($distributionSchedules as $schedule)
                        <tr x-show="matchRow('{{ addslashes($schedule->code) }}', '{{ addslashes($schedule->officer->name ?? '') }}', '{{ addslashes($schedule->depot->name ?? '') }}', '{{ $schedule->scheduled_date->format('Y-m-d') }}', '{{ $schedule->status }}')" 
                            class="hover:bg-slate-50/80 transition-colors">
                            
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md text-xs border border-slate-200">
                                    {{ $schedule->code }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="font-semibold text-slate-800 text-sm">{{ $schedule->scheduled_date->format('d M Y') }}</div>
                                <div class="text-[11px] text-slate-400">{{ $schedule->scheduled_date->translatedFormat('l') }}</div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-2.5">
                                    <div class="w-7 h-7 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ substr($schedule->officer->name ?? '?', 0, 2) }}
                                    </div>
                                    <div class="text-sm font-medium text-slate-700">{{ $schedule->officer->name ?? 'Petugas Dihapus' }}</div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold text-indigo-700 bg-indigo-50 px-2.5 py-1 rounded-full border border-indigo-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $schedule->depot->name ?? 'Depot Dihapus' }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-sm border border-emerald-200">
                                    {{ number_format($schedule->total_portions) }} <span class="text-xs font-normal">Porsi</span>
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                @if ($schedule->runs()->exists())
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 border border-blue-200 shadow-2xs">
                                        <svg class="w-3.5 h-3.5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Rute Digenerate
                                    </span>
                                @else
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200">
                                        <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                        Belum Digenerate
                                    </span>
                                @endif
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <x-badge :variant="$schedule->status === 'scheduled' ? 'active' : ($schedule->status === 'cancelled' ? 'inactive' : 'default')">
                                    {{ str($schedule->status)->replace('_', ' ')->title() }}
                                </x-badge>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('distribution-schedules.show', $schedule) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Lihat Detail & Tujuan">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    <a href="{{ route('distribution-schedules.edit', $schedule) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Jadwal">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    @if ($schedule->status !== 'cancelled')
                                        <form method="POST" action="{{ route('distribution-schedules.destroy', $schedule) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin membatalkan jadwal distribusi ini?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Batalkan Jadwal">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $distributionSchedules->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
