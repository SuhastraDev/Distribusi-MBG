<x-layouts.app title="Detail Jadwal: {{ $distributionSchedule->code }}" breadcrumb="Operasional / Jadwal Distribusi / Detail">
    <div class="max-w-6xl mx-auto space-y-6">
        <!-- Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-indigo-100 text-indigo-800 font-bold flex items-center justify-center text-lg shadow-inner">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-800">{{ $distributionSchedule->code }}</h2>
                        <x-badge :variant="$distributionSchedule->status === 'scheduled' ? 'active' : ($distributionSchedule->status === 'cancelled' ? 'inactive' : 'default')">
                            {{ str($distributionSchedule->status)->replace('_', ' ')->title() }}
                        </x-badge>
                    </div>
                    <p class="text-xs text-slate-500 font-mono">
                        Tanggal: {{ $distributionSchedule->scheduled_date->format('d M Y') }} &bull; Total: {{ number_format($distributionSchedule->total_portions) }} Porsi
                    </p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('distribution-schedules.index') }}">
                    &larr; Kembali
                </x-button>
                @if ($distributionSchedule->status !== 'cancelled')
                    <x-button variant="primary" size="sm" href="{{ route('distribution-schedules.edit', $distributionSchedule) }}">
                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        Edit Jadwal
                    </x-button>
                @endif
            </div>
        </div>

        <!-- Route Generation Status Banner -->
        @php
            $existingRun = $distributionSchedule->runs()->first();
        @endphp

        @if ($existingRun)
            <div class="p-4 rounded-2xl bg-blue-50 border border-blue-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-blue-900">Rute Pengiriman Telah Digenerate</h4>
                        <p class="text-xs text-blue-700">Jadwal ini telah diubah menjadi distribusi aktual dengan kode <strong class="font-mono">{{ $existingRun->code }}</strong> (Status: {{ strtoupper($existingRun->status) }}).</p>
                    </div>
                </div>
                <x-button variant="primary" size="sm" href="{{ route('distribution-runs.show', $existingRun) }}" class="bg-blue-600 hover:bg-blue-700 shrink-0">
                    Lihat Run & Rute Aktual &rarr;
                </x-button>
            </div>
        @elseif ($distributionSchedule->status === 'scheduled' && $distributionSchedule->destinations->isNotEmpty())
            <div class="p-4 rounded-2xl bg-indigo-50 border border-indigo-200 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 shadow-2xs">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-600 text-white flex items-center justify-center shrink-0 shadow-sm">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                    <div>
                        <h4 class="text-sm font-bold text-indigo-950">Siap Digenerate Menjadi Rute Aktual</h4>
                        <p class="text-xs text-indigo-700">Jadwal telah berstatus Terjadwal dan memiliki {{ $distributionSchedule->destinations->count() }} titik tujuan. Anda dapat membuat run distribusi sekarang.</p>
                    </div>
                </div>
                <x-button variant="primary" size="sm" href="{{ route('distribution-runs.create', ['schedule_id' => $distributionSchedule->id]) }}" class="bg-indigo-600 hover:bg-indigo-700 shrink-0">
                    Buat Distribusi Aktual (Run) &rarr;
                </x-button>
            </div>
        @endif

        <!-- Content Grid -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Left 2 Cols: Destinations Table & Add Form -->
            <div class="lg:col-span-2 space-y-6">
                <x-card title="Daftar Tujuan Distribusi" subtitle="Urutan titik sekolah penerima dalam jadwal ini">
                    @if ($distributionSchedule->destinations->isEmpty())
                        <x-empty-state title="Belum Ada Tujuan" description="Jadwal ini belum memiliki daftar sekolah penerima. Silakan tambahkan tujuan melalui form di bawah." />
                    @else
                        <x-table :headers="['#', 'Sekolah / Penerima', 'Titik Lokasi', 'Alokasi Porsi', 'Aksi']">
                            @foreach ($distributionSchedule->destinations->sortBy('sequence_order') as $destination)
                                <tr class="hover:bg-slate-50/80 transition-colors">
                                    <td class="px-4 py-3 whitespace-nowrap font-mono text-xs font-bold text-slate-500">
                                        {{ $destination->sequence_order }}
                                    </td>
                                    
                                    <td class="px-4 py-3 whitespace-nowrap font-semibold text-slate-800 text-sm">
                                        {{ $destination->recipient->name ?? 'Penerima Terhapus' }}
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-xs">
                                        <a href="{{ route('locations.show', $destination->location_id) }}" class="text-blue-700 font-medium hover:underline">
                                            {{ $destination->location->name ?? '-' }}
                                        </a>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap">
                                        <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded bg-emerald-50 text-emerald-700 font-bold text-xs border border-emerald-200">
                                            {{ number_format($destination->portion_count) }} Porsi
                                        </span>
                                    </td>

                                    <td class="px-4 py-3 whitespace-nowrap text-right">
                                        @if (!$existingRun && $distributionSchedule->status !== 'cancelled')
                                            <form method="POST" action="{{ route('distribution-schedules.destinations.destroy', [$distributionSchedule, $destination]) }}" 
                                                  onsubmit="return confirm('Hapus tujuan ini dari jadwal? Total porsi akan disesuaikan otomatis.');" class="inline">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="p-1 rounded text-slate-400 hover:text-red-600 hover:bg-red-50 transition-colors" title="Hapus Tujuan">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                </button>
                                            </form>
                                        @else
                                            <span class="text-[11px] text-slate-400 italic">Terkunci</span>
                                        @endif
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @endif
                </x-card>

                <!-- Add Destination Form Card -->
                @if (!$existingRun && $distributionSchedule->status !== 'cancelled')
                    <x-card title="Tambah Tujuan Baru ke Jadwal" subtitle="Pilih sekolah atau kelompok penerima tambahan">
                        <form method="POST" action="{{ route('distribution-schedules.destinations.store', $distributionSchedule) }}" class="space-y-4">
                            @csrf
                            <div class="flex flex-col sm:flex-row gap-3 items-end">
                                <div class="flex-1 w-full">
                                    <x-select label="Pilih Penerima Aktif" name="recipient_id" required>
                                        <option value="">-- Pilih Sekolah / Kelompok --</option>
                                        @foreach ($recipients as $recipient)
                                            <option value="{{ $recipient->id }}">
                                                {{ $recipient->name }} &mdash; {{ $recipient->location->name }} ({{ number_format($recipient->portion_count) }} porsi)
                                            </option>
                                        @endforeach
                                    </x-select>
                                </div>
                                <div class="shrink-0 w-full sm:w-auto">
                                    <x-button type="submit" variant="primary" class="w-full sm:w-auto">
                                        <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                        Tambah Tujuan
                                    </x-button>
                                </div>
                            </div>
                        </form>
                    </x-card>
                @endif
            </div>

            <!-- Right Col: Schedule Info & Actions -->
            <div class="space-y-6">
                <x-card title="Ringkasan Operasional" subtitle="Parameter jadwal logistik">
                    <dl class="divide-y divide-slate-100 text-sm space-y-3">
                        <div class="pt-2 flex justify-between">
                            <dt class="text-slate-500 font-medium">Kode Jadwal</dt>
                            <dd class="font-mono font-bold text-slate-800">{{ $distributionSchedule->code }}</dd>
                        </div>

                        <div class="pt-3 flex justify-between">
                            <dt class="text-slate-500 font-medium">Tanggal Distribusi</dt>
                            <dd class="font-semibold text-slate-800">{{ $distributionSchedule->scheduled_date->format('d/m/Y') }}</dd>
                        </div>

                        <div class="pt-3 flex justify-between items-center">
                            <dt class="text-slate-500 font-medium">Depot Asal (Kitchen)</dt>
                            <dd>
                                <a href="{{ route('locations.show', $distributionSchedule->depot_location_id) }}" class="font-bold text-indigo-700 bg-indigo-50 px-2 py-0.5 rounded text-xs hover:underline">
                                    {{ $distributionSchedule->depot->name ?? '-' }}
                                </a>
                            </dd>
                        </div>

                        <div class="pt-3 flex justify-between items-center">
                            <dt class="text-slate-500 font-medium">Petugas Lapangan</dt>
                            <dd>
                                <a href="{{ route('officers.show', $distributionSchedule->officer_id) }}" class="font-bold text-emerald-700 bg-emerald-50 px-2 py-0.5 rounded text-xs hover:underline">
                                    {{ $distributionSchedule->officer->name ?? '-' }}
                                </a>
                            </dd>
                        </div>

                        <div class="pt-3 flex justify-between items-center">
                            <dt class="text-slate-500 font-medium">Total Porsi Terhitung</dt>
                            <dd>
                                <span class="font-black text-slate-800 text-base bg-amber-50 text-amber-800 px-2.5 py-0.5 rounded-lg border border-amber-200">
                                    {{ number_format($distributionSchedule->total_portions) }} Porsi
                                </span>
                            </dd>
                        </div>

                        <div class="pt-3">
                            <dt class="text-slate-500 font-medium text-xs uppercase mb-1">Catatan Tambahan</dt>
                            <dd class="text-slate-700 text-xs bg-slate-50 p-2.5 rounded-xl border border-slate-100 leading-relaxed">
                                {{ $distributionSchedule->notes ?: 'Tidak ada instruksi khusus.' }}
                            </dd>
                        </div>
                    </dl>

                    @if ($distributionSchedule->status !== 'cancelled' && !$existingRun)
                        <x-slot name="footer">
                            <form method="POST" action="{{ route('distribution-schedules.destroy', $distributionSchedule) }}" class="w-full"
                                  onsubmit="return confirm('Apakah Anda yakin ingin membatalkan jadwal ini? Rencana pengiriman akan dihentikan.');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" size="sm" class="w-full justify-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Batalkan Rencana Jadwal
                                </x-button>
                            </form>
                        </x-slot>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
