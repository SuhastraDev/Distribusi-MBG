<x-layouts.app title="Buat Distribusi Aktual (Run)" breadcrumb="Operasional / Distribusi Aktual / Buat Baru">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Inisiasi Distribusi Aktual dari Jadwal</h2>
                <p class="text-sm text-slate-500">Pilih jadwal rencana pengiriman yang siap dijalankan di lapangan oleh petugas</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('distribution-runs.index') }}">
                &larr; Kembali ke Daftar
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('distribution-runs.store') }}" class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
                @csrf

                <div>
                    <x-select label="Pilih Rencana Jadwal Distribusi" name="distribution_schedule_id" required helper="Hanya menampilkan jadwal berstatus 'Terjadwal' yang belum dibuatkan distribusi run.">
                        <option value="">-- Pilih Jadwal Pengiriman --</option>
                        @foreach ($schedules as $schedule)
                            <option value="{{ $schedule->id }}" @selected((int) old('distribution_schedule_id', request('schedule_id')) === $schedule->id)>
                                {{ $schedule->code }} &bull; Tgl: {{ $schedule->scheduled_date->format('d/m/Y') }} &bull; Petugas: {{ $schedule->officer->name }} &bull; Depot: {{ $schedule->depot->name }} ({{ $schedule->destinations->count() }} Sekolah)
                            </option>
                        @endforeach
                    </x-select>
                </div>

                <div>
                    <x-textarea 
                        label="Catatan Awal Eksekusi (Opsional)" 
                        name="notes" 
                        rows="3" 
                        placeholder="Tambahkan instruksi lapangan, kondisi kendaraan, atau catatan pengiriman..."
                        helper="Catatan ini akan tersimpan pada arsip logistik distribusi aktual."
                    >{{ old('notes') }}</x-textarea>
                </div>

                <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
                    <x-button variant="outline" href="{{ route('distribution-runs.index') }}">
                        Batal
                    </x-button>

                    <button type="submit" 
                            class="inline-flex items-center justify-center gap-2 py-2 px-6 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md transition-all duration-200 disabled:opacity-70 cursor-pointer"
                            :disabled="loading">
                        <span x-show="!loading" class="inline-flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Buat Distribusi Sekarang
                        </span>
                        <span x-show="loading" style="display: none;" class="inline-flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memproses...
                        </span>
                    </button>
                </div>
            </form>
        </x-card>
    </div>
</x-layouts.app>
