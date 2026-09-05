<x-layouts.app title="Data Penerima MBG" breadcrumb="Data Master / Penerima MBG">
    <div x-data="{
        search: '',
        locationFilter: 'all',
        matchRow(code, name, locationName) {
            const matchesSearch = this.search === '' || 
                code.toLowerCase().includes(this.search.toLowerCase()) || 
                name.toLowerCase().includes(this.search.toLowerCase()) ||
                locationName.toLowerCase().includes(this.search.toLowerCase());
            
            const matchesLocation = this.locationFilter === 'all' || locationName === this.locationFilter;
            return matchesSearch && matchesLocation;
        }
    }" class="space-y-6">

        <!-- Top Panel & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Penerima MBG</h2>
                <p class="text-sm text-slate-500">Kelola kelompok sekolah atau lembaga penerima manfaat dan alokasi porsi harian</p>
            </div>
            <div class="shrink-0">
                <x-button variant="primary" href="{{ route('recipients.create') }}" class="w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Penerima Baru
                </x-button>
            </div>
        </div>

        <!-- Filter & Search Card -->
        <div class="bg-white p-4 rounded-2xl border border-slate-200/80 shadow-2xs flex flex-col sm:flex-row gap-4 items-center justify-between">
            <div class="relative w-full sm:w-80">
                <div class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" 
                       x-model="search" 
                       placeholder="Cari kode, nama, atau sekolah..." 
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors bg-slate-50/50">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label class="text-xs font-semibold text-slate-500 shrink-0">Filter Lokasi:</label>
                <select x-model="locationFilter" class="w-full sm:w-auto px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                    <option value="all">Semua Lokasi / Sekolah</option>
                    @php
                        $uniqueLocations = $recipients->pluck('location.name')->unique()->filter();
                    @endphp
                    @foreach ($uniqueLocations as $locName)
                        <option value="{{ $locName }}">{{ $locName }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Recipients Table -->
        <x-card padding="p-0">
            @if ($recipients->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Belum Ada Data Penerima" 
                        description="Kelola alokasi porsi makanan per sekolah atau lembaga. Klik tombol di atas untuk mendaftarkan penerima manfaat baru."
                    >
                        <x-button variant="primary" size="sm" href="{{ route('recipients.create') }}" class="mt-2">
                            Tambah Penerima
                        </x-button>
                    </x-empty-state>
                </div>
            @else
                <x-table :headers="['Kode Penerima', 'Nama Lembaga / Kelompok', 'Lokasi Sekolah', 'Alokasi Porsi', 'Status', 'Aksi']">
                    @foreach ($recipients as $recipient)
                        <tr x-show="matchRow('{{ addslashes($recipient->code) }}', '{{ addslashes($recipient->name) }}', '{{ addslashes($recipient->location->name ?? '') }}')" 
                            class="hover:bg-slate-50/80 transition-colors">
                            
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md text-xs border border-slate-200">
                                    {{ $recipient->code }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ substr($recipient->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800 text-sm">{{ $recipient->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ str($recipient->notes ?? 'Tanpa catatan tambahan')->limit(30) }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <a href="{{ route('locations.show', $recipient->location_id) }}" class="inline-flex items-center gap-1.5 text-xs font-semibold text-emerald-700 hover:underline bg-emerald-50 px-2.5 py-1 rounded-full border border-emerald-200">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                    {{ $recipient->location->name ?? 'Lokasi Terhapus' }}
                                </a>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-emerald-50 text-emerald-700 font-bold text-sm border border-emerald-200">
                                    {{ number_format($recipient->portion_count) }} <span class="text-xs font-normal">Porsi</span>
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <x-badge :variant="$recipient->status">
                                    {{ $recipient->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('recipients.show', $recipient) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    <a href="{{ route('recipients.edit', $recipient) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Penerima">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    @if ($recipient->status === 'active')
                                        <form method="POST" action="{{ route('recipients.destroy', $recipient) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan penerima {{ addslashes($recipient->name) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Nonaktifkan Penerima">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                            </button>
                                        </form>
                                    @endif

                                    <form method="POST" action="{{ route('recipients.force-delete', $recipient) }}"
                                          onsubmit="return confirm('HAPUS PERMANEN penerima {{ addslashes($recipient->name) }}? Data yang sudah dihapus tidak bisa dikembalikan.');" class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="p-1.5 rounded-lg text-slate-500 hover:text-red-700 hover:bg-red-100 transition-colors" title="Hapus Permanen">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </x-table>

                <div class="p-4 border-t border-slate-100 bg-slate-50/50">
                    {{ $recipients->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
