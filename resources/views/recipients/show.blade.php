<x-layouts.app title="Detail Penerima: {{ $recipient->name }}" breadcrumb="Data Master / Penerima MBG / Detail">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-amber-100 text-amber-800 font-bold flex items-center justify-center text-lg shadow-inner">
                    {{ substr($recipient->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-800">{{ $recipient->name }}</h2>
                        <x-badge :variant="$recipient->status">
                            {{ $recipient->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>
                    <p class="text-xs text-slate-500 font-mono">Kode: {{ $recipient->code }} &bull; Alokasi: {{ number_format($recipient->portion_count) }} Porsi</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('recipients.index') }}">
                    &larr; Kembali
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route('recipients.edit', $recipient) }}">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Data
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <!-- Location & Alocation Info Card -->
                <x-card title="Informasi Alokasi & Lokasi" subtitle="Detail sasaran pengiriman paket makanan bergizi">
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Kode Penerima</dt>
                            <dd class="col-span-2 font-semibold text-slate-800 font-mono bg-slate-50 px-2 py-0.5 rounded w-fit">{{ $recipient->code }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Nama Lembaga</dt>
                            <dd class="col-span-2 font-semibold text-slate-800">{{ $recipient->name }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Lokasi Sekolah / Titik</dt>
                            <dd class="col-span-2">
                                <a href="{{ route('locations.show', $recipient->location_id) }}" class="inline-flex items-center gap-1 font-semibold text-emerald-600 hover:underline">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                    {{ $recipient->location->name ?? 'Lokasi tidak ditemukan' }}
                                </a>
                                <div class="text-xs text-slate-400 mt-0.5">{{ $recipient->location->address ?? '' }}</div>
                            </dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Alokasi Porsi</dt>
                            <dd class="col-span-2">
                                <span class="inline-flex items-center gap-1 px-3 py-1 rounded-lg bg-emerald-50 text-emerald-800 font-bold text-base border border-emerald-200">
                                    {{ number_format($recipient->portion_count) }} <span class="text-xs font-normal">Porsi / Hari</span>
                                </span>
                            </dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Catatan Khusus</dt>
                            <dd class="col-span-2 text-slate-800 leading-relaxed">{{ $recipient->notes ?? 'Tidak ada catatan tambahan' }}</dd>
                        </div>
                    </dl>
                </x-card>
            </div>

            <!-- Sidebar Action Card -->
            <div class="space-y-6">
                <x-card title="Status Keaktifan" subtitle="Kontrol distribusi">
                    <div class="space-y-4 text-sm">
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Status Saat Ini</div>
                            <div class="mt-1">
                                <x-badge :variant="$recipient->status">
                                    {{ $recipient->status === 'active' ? 'Aktif Menerima MBG' : 'Dinonaktifkan' }}
                                </x-badge>
                            </div>
                        </div>

                        <div class="text-xs text-slate-500">
                            @if ($recipient->status === 'active')
                                Kelompok sekolah ini akan dimasukkan secara default dalam penghitungan alokasi porsi dan titik pengiriman rute.
                            @else
                                Kelompok ini disembunyikan dari pengiriman baru.
                            @endif
                        </div>
                    </div>

                    @if ($recipient->status === 'active')
                        <x-slot name="footer">
                            <form method="POST" action="{{ route('recipients.destroy', $recipient) }}" class="w-full"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan penerima {{ addslashes($recipient->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" size="sm" class="w-full justify-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Nonaktifkan Penerima Ini
                                </x-button>
                            </form>
                        </x-slot>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
