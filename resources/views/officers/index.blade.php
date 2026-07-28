<x-layouts.app title="Data Petugas Distribusi" breadcrumb="Data Master / Petugas">
    <div x-data="{
        search: '',
        statusFilter: 'all',
        matchRow(code, name, email, status) {
            const matchesSearch = this.search === '' || 
                code.toLowerCase().includes(this.search.toLowerCase()) || 
                name.toLowerCase().includes(this.search.toLowerCase()) || 
                email.toLowerCase().includes(this.search.toLowerCase());
            
            const matchesStatus = this.statusFilter === 'all' || status === this.statusFilter;
            return matchesSearch && matchesStatus;
        }
    }" class="space-y-6">

        <!-- Top Panel & Actions -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Daftar Petugas Distribusi</h2>
                <p class="text-sm text-slate-500">Kelola akun personel lapangan pengirim paket makanan bergizi gratis</p>
            </div>
            <div class="shrink-0">
                <x-button variant="primary" href="{{ route('officers.create') }}" class="w-full sm:w-auto">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Tambah Petugas Baru
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
                       placeholder="Cari kode, nama, atau email..." 
                       class="w-full pl-10 pr-4 py-2 text-sm rounded-xl border border-slate-200 focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:border-emerald-500 transition-colors bg-slate-50/50">
            </div>

            <div class="flex items-center gap-2 w-full sm:w-auto">
                <label class="text-xs font-semibold text-slate-500 shrink-0">Filter Status:</label>
                <select x-model="statusFilter" class="w-full sm:w-auto px-3 py-2 text-sm rounded-xl border border-slate-200 bg-white focus:outline-none focus:ring-2 focus:ring-emerald-500 text-slate-700 font-medium">
                    <option value="all">Semua Status</option>
                    <option value="active">Aktif</option>
                    <option value="inactive">Nonaktif</option>
                </select>
            </div>
        </div>

        <!-- Officers Table -->
        <x-card padding="p-0">
            @if ($officers->isEmpty())
                <div class="p-8">
                    <x-empty-state 
                        title="Belum Ada Data Petugas" 
                        description="Data personel lapangan belum ditambahkan. Klik tombol di atas untuk mendaftarkan petugas baru."
                    >
                        <x-button variant="primary" size="sm" href="{{ route('officers.create') }}" class="mt-2">
                            Tambah Petugas
                        </x-button>
                    </x-empty-state>
                </div>
            @else
                <x-table :headers="['Kode Petugas', 'Nama Personel', 'Email Login', 'Kontak / HP', 'Status', 'Aksi']">
                    @foreach ($officers as $officer)
                        <tr x-show="matchRow('{{ addslashes($officer->officer_code) }}', '{{ addslashes($officer->name) }}', '{{ addslashes($officer->user->email ?? '') }}', '{{ $officer->status }}')" 
                            class="hover:bg-slate-50/80 transition-colors">
                            
                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <span class="font-bold text-slate-800 bg-slate-100 px-2.5 py-1 rounded-md text-xs border border-slate-200">
                                    {{ $officer->officer_code }}
                                </span>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-xs shrink-0">
                                        {{ substr($officer->name, 0, 2) }}
                                    </div>
                                    <div>
                                        <div class="font-semibold text-slate-800 text-sm">{{ $officer->name }}</div>
                                        <div class="text-[11px] text-slate-400">{{ str($officer->address ?? 'Tidak ada alamat')->limit(30) }}</div>
                                    </div>
                                </div>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-slate-600">
                                {{ $officer->user->email ?? '-' }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-sm text-slate-600 font-mono">
                                {{ $officer->phone ?? '-' }}
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap">
                                <x-badge :variant="$officer->status">
                                    {{ $officer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                                </x-badge>
                            </td>

                            <td class="px-4 py-3.5 whitespace-nowrap text-right text-sm font-medium">
                                <div class="flex items-center gap-2 justify-end">
                                    <a href="{{ route('officers.show', $officer) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="Lihat Detail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </a>

                                    <a href="{{ route('officers.edit', $officer) }}" 
                                       class="p-1.5 rounded-lg text-slate-500 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="Edit Petugas">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </a>

                                    @if ($officer->status === 'active')
                                        <form method="POST" action="{{ route('officers.destroy', $officer) }}" 
                                              onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan petugas {{ addslashes($officer->name) }}?');" class="inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" 
                                                    class="p-1.5 rounded-lg text-slate-500 hover:text-red-600 hover:bg-red-50 transition-colors" title="Nonaktifkan Petugas">
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
                    {{ $officers->links() }}
                </div>
            @endif
        </x-card>

    </div>
</x-layouts.app>
