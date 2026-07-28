<x-layouts.app title="Data Lokasi Distribusi" breadcrumb="Data Master / Lokasi & Depot">
    <div x-data="{
        search: '',
        typeFilter: 'all',
        matchRow(code, name, type) {
            const matchesSearch = this.search === '' || 
                code.toLowerCase().includes(this.search.toLowerCase()) || 
                name.toLowerCase().includes(this.search.toLowerCase());
            
            const matchesType = this.typeFilter === 'all' || type === this.typeFilter;
            return matchesSearch && matchesType;
        }
    }" class="space-y-6">

        <!-- Top Panel & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Data Lokasi Distribusi</h2>
                <p class="text-sm text-slate-500">Kelola koordinat GPS depot asal masakan dan titik sekolah penerima MBG</p>
            </div>
            <div class="shrink-0">
                <x-button variant="primary" href="{{ route('locations.create') }}" class="w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Lokasi Baru
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
                       placeholder="Cari kode atau nama lokasi..." 
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors bg-slate-50/50">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label class="text-xs font-semibold text-slate-500 shrink-0">Filter Tipe:</label>
                <select x-model="typeFilter" class="w-full sm:w-auto px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                    <option value="all">Semua Tipe</option>
                    <option value="depot">Depot Masakan / Dapur</option>
                    <option value="school">Sekolah Penerima</option>
                    <option value="other">Lainnya</option>
                </select>
            </div>
        </div>

        <!-- Locations Table -->
        <x-card padding="p-0">
            @if ($locations->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Belum Ada Data Lokasi" 
                        description="Titik depot dan sekolah belum tercatat dalam sistem logistik. Silakan tambahkan lokasi baru."
                    >
                        <x-button variant="primary" size="sm" href="{{ route('locations.create') }}" class="mt-2">
                            Tambah Lokasi
                        </x-button>
                    </x-empty-state>
                </div>
            @else
                <x-table :headers="['Kode Lokasi', 'Nama Titik', 'Tipe Lokasi', 'Koordinat GPS', 'Status', 'Aksi']">
                    @foreach ($locations as $location)
                        <tr x-show="matchRow('{{ addslashes($location->code) }}', '{{ addslashes($location->name) }}', '{{ $location->type }}')" 
                            class="hover:bg-slate-50/80 transition-colors">
                            
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md text-xs border border-slate-200">
                                    {{ $location->code }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-xl {{ $location->type === 'depot' ? 'bg-blue-100 text-blue-700' : 'bg-emerald-100 text-emerald-700' }} font-bold flex items-center justify-center shrink-0">
                                        @if ($location->type === 'depot')
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                                        @else
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                                        @endif
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800 text-sm">{{ $location->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ str($location->address ?? 'Alamat tidak tercatat')->limit(35) }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <x-badge :variant="$location->type === 'depot' ? 'depot' : ($location->type === 'school' ? 'sekolah' : 'default')">
                                    {{ $location->typeLabel() }}
                                </x-badge>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-xs font-mono text-slate-600">
                                <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" class="inline-flex items-center gap-1 text-emerald-600 hover:text-emerald-800 hover:underline">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    <span>{{ number_format((float)$location->latitude, 5) }}, {{ number_format((float)$location->longitude, 5) }}</span>
                                </a>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <x-badge :variant="$location->status">
                                    {{ $location->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('locations.show', $location) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Lihat Detail & Peta">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    <a href="{{ route('locations.edit', $location) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Lokasi">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    @if ($location->status === 'active')
                                        <form method="POST" action="{{ route('locations.destroy', $location) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan lokasi {{ addslashes($location->name) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Nonaktifkan Lokasi">
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
                    {{ $locations->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
