<x-layouts.app title="Detail Petugas: {{ $officer->name }}" breadcrumb="Data Master / Petugas / Detail">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl bg-emerald-100 text-emerald-800 font-bold flex items-center justify-center text-lg shadow-inner">
                    {{ substr($officer->name, 0, 2) }}
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-800">{{ $officer->name }}</h2>
                        <x-badge :variant="$officer->status">
                            {{ $officer->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>
                    <p class="text-xs text-slate-500 font-mono">Kode: {{ $officer->officer_code }} &bull; Role: {{ $officer->user->role?->display_name ?? 'Petugas' }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('officers.index') }}">
                    &larr; Kembali
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route('officers.edit', $officer) }}">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Data
                </x-button>
            </div>
        </div>

        <!-- Profile Details Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <x-card title="Informasi Profil & Kontak" subtitle="Data pribadi dan kontak operasional petugas lapangan">
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Kode Identifier</dt>
                            <dd class="col-span-2 font-semibold text-slate-800 font-mono bg-slate-50 px-2 py-0.5 rounded w-fit">{{ $officer->officer_code }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Nama Lengkap</dt>
                            <dd class="col-span-2 font-semibold text-slate-800">{{ $officer->name }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Nomor Telepon / HP</dt>
                            <dd class="col-span-2 text-slate-800 font-mono">{{ $officer->phone ?? 'Tidak dicantumkan' }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Alamat Domisili</dt>
                            <dd class="col-span-2 text-slate-800 leading-relaxed">{{ $officer->address ?? 'Tidak ada alamat tercatat' }}</dd>
                        </div>
                    </dl>
                </x-card>

                <x-card title="Riwayat Tugas & Pengiriman" subtitle="Aktivitas distribusi terbaru yang dilakukan petugas ini">
                    @php
                        $recentRuns = \App\Models\DistributionRun::where('officer_id', $officer->id)
                            ->with('schedule.depot')
                            ->latest()
                            ->limit(5)
                            ->get();
                    @endphp

                    @if ($recentRuns->isEmpty())
                        <x-empty-state title="Belum Ada Riwayat Tugas" description="Petugas ini belum menjalankan jadwal pengiriman distribusi MBG." />
                    @else
                        <x-table :headers="['Kode Run', 'Depot Asal', 'Tanggal', 'Status', '']">
                            @foreach ($recentRuns as $run)
                                <tr>
                                    <td class="px-4 py-2.5 font-mono text-xs font-bold">{{ $run->code }}</td>
                                    <td class="px-4 py-2.5 text-xs">{{ $run->schedule->depot->name ?? '-' }}</td>
                                    <td class="px-4 py-2.5 text-xs text-slate-500">{{ $run->schedule->scheduled_date->format('d/m/Y') }}</td>
                                    <td class="px-4 py-2.5">
                                        <x-badge :variant="$run->status">{{ strtoupper($run->status) }}</x-badge>
                                    </td>
                                    <td class="px-4 py-2.5 text-right">
                                        <a href="{{ route('distribution-runs.show', $run) }}" class="text-xs text-emerald-600 hover:underline">Lihat</a>
                                    </td>
                                </tr>
                            @endforeach
                        </x-table>
                    @endif
                </x-card>
            </div>

            <!-- Sidebar Account Card -->
            <div class="space-y-6">
                <x-card title="Akun Pengguna (Login)" subtitle="Kredensial untuk masuk sistem">
                    <div class="space-y-4 text-sm">
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Email Login</div>
                            <div class="mt-1 font-mono font-medium text-slate-800 break-all bg-slate-50 p-2 rounded-lg border border-slate-100">
                                {{ $officer->user->email ?? '-' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Terakhir Login</div>
                            <div class="mt-1 text-slate-700">
                                {{ $officer->user->last_login_at ? $officer->user->last_login_at->format('d M Y, H:i') : 'Belum pernah login' }}
                            </div>
                        </div>

                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Status Akun</div>
                            <div class="mt-1">
                                <span class="inline-flex items-center gap-1.5 text-xs font-semibold {{ $officer->user->isActive() ? 'text-emerald-600' : 'text-red-600' }}">
                                    <span class="w-2 h-2 rounded-full {{ $officer->user->isActive() ? 'bg-emerald-500' : 'bg-red-500' }}"></span>
                                    {{ $officer->user->isActive() ? 'Akun Aktif' : 'Akun Dinonaktifkan' }}
                                </span>
                            </div>
                        </div>
                    </div>

                    @if ($officer->status === 'active')
                        <x-slot name="footer">
                            <form method="POST" action="{{ route('officers.destroy', $officer) }}" class="w-full"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan petugas ini beserta akun loginnya?');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" size="sm" class="w-full justify-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Nonaktifkan Petugas Ini
                                </x-button>
                            </form>
                        </x-slot>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>
