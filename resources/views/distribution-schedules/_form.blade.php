@csrf

@php
    $selectedRecipientIds = collect(old(
        'recipient_ids',
        isset($distributionSchedule)
            ? $distributionSchedule->destinations->pluck('recipient_id')->all()
            : []
    ))->map(fn ($id) => (int) $id)->all();
@endphp

<div class="space-y-6" x-data="{ 
    loading: false,
    selected: [{{ implode(',', $selectedRecipientIds) }}],
    recipients: [
        @foreach ($recipients as $recipient)
            { id: {{ $recipient->id }}, name: '{{ addslashes($recipient->name) }}', location: '{{ addslashes($recipient->location->name ?? '') }}', portions: {{ (int)$recipient->portion_count }} },
        @endforeach
    ],
    get totalPortions() {
        return this.recipients
            .filter(r => this.selected.map(Number).includes(r.id))
            .reduce((acc, curr) => acc + curr.portions, 0);
    },
    toggleSelectAll() {
        if (this.selected.length === this.recipients.length) {
            this.selected = [];
        } else {
            this.selected = this.recipients.map(r => r.id);
        }
    }
}" @submit="loading = true">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kode Jadwal -->
        <div>
            <x-input 
                label="Kode Jadwal Distribusi" 
                name="code" 
                value="{{ old('code', $distributionSchedule->code ?? 'SCH-' . now()->format('Ymd') . '-' . strtoupper(substr(uniqid(), -4))) }}" 
                required 
                placeholder="contoh: SCH-20260727-ABCD" 
                helper="Kode identifikasi unik untuk jadwal distribusi ini."
            />
        </div>

        <!-- Tanggal Distribusi -->
        <div>
            <x-input 
                label="Tanggal Distribusi" 
                name="scheduled_date" 
                type="date" 
                value="{{ old('scheduled_date', isset($distributionSchedule) ? $distributionSchedule->scheduled_date->format('Y-m-d') : now()->toDateString()) }}" 
                required 
                helper="Tanggal rencana pengiriman dilakukan di lapangan."
            />
        </div>

        <!-- Petugas Distribusi -->
        <div>
            <x-select label="Petugas Lapangan" name="officer_id" required helper="Personel yang bertugas mengirimkan paket makanan.">
                <option value="">-- Pilih Petugas --</option>
                @foreach ($officers as $officer)
                    <option value="{{ $officer->id }}" @selected((int) old('officer_id', $distributionSchedule->officer_id ?? 0) === $officer->id)>
                        {{ $officer->name }} (Kode: {{ $officer->officer_code }})
                    </option>
                @endforeach
            </x-select>
        </div>

        <!-- Depot Awal -->
        <div>
            <x-select label="Depot Dapur Asal (Kitchen)" name="depot_location_id" required helper="Titik awal pengambilan makanan sebelum pengiriman.">
                <option value="">-- Pilih Depot Asal --</option>
                @foreach ($depots as $depot)
                    <option value="{{ $depot->id }}" @selected((int) old('depot_location_id', $distributionSchedule->depot_location_id ?? 0) === $depot->id)>
                        {{ $depot->name }} ({{ $depot->code }})
                    </option>
                @endforeach
            </x-select>
        </div>

        <!-- Status -->
        <div>
            <x-select label="Status Rencana Jadwal" name="status" required>
                <option value="draft" @selected(old('status', $distributionSchedule->status ?? 'draft') === 'draft')>Draft (Masih dalam Penyusunan)</option>
                <option value="scheduled" @selected(old('status', $distributionSchedule->status ?? 'draft') === 'scheduled')>Terjadwal (Siap Digenerate Rute)</option>
                <option value="cancelled" @selected(old('status', $distributionSchedule->status ?? 'draft') === 'cancelled')>Dibatalkan (Batal Kirim)</option>
            </x-select>
        </div>

        <!-- Catatan -->
        <div>
            <x-textarea 
                label="Catatan Jadwal (Opsional)" 
                name="notes" 
                rows="2"
                placeholder="Instruksi tambahan untuk petugas pengirim..."
            >{{ old('notes', $distributionSchedule->notes ?? '') }}</x-textarea>
        </div>
    </div>

    <!-- Recipients Checklist Card Area -->
    <div class="pt-4 border-t border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
            <div>
                <h4 class="text-sm font-bold text-slate-800 flex items-center gap-2">
                    Tujuan Distribusi (Sekolah Penerima)
                    <span class="px-2 py-0.5 rounded-full text-xs font-extrabold bg-indigo-100 text-indigo-700" x-text="selected.length + ' Tujuan Dipilih'"></span>
                </h4>
                <p class="text-xs text-slate-500">Pilih minimal satu penerima aktif untuk dimasukkan dalam jadwal rute pengiriman ini.</p>
            </div>

            <div class="flex items-center gap-3 bg-emerald-50 px-4 py-2 rounded-xl border border-emerald-200 shadow-2xs shrink-0">
                <div class="text-right">
                    <div class="text-[10px] uppercase font-bold text-emerald-600 tracking-wider">Total Porsi Terpilih</div>
                    <div class="text-lg font-black text-emerald-800" x-text="totalPortions.toLocaleString() + ' Porsi'"></div>
                </div>
                <button type="button" @click="toggleSelectAll()" class="text-xs font-semibold text-emerald-700 hover:text-emerald-900 bg-white px-2.5 py-1.5 rounded-lg border border-emerald-300 shadow-2xs transition-colors cursor-pointer">
                    <span x-text="selected.length === recipients.length ? 'Batal Semua' : 'Pilih Semua'"></span>
                </button>
            </div>
        </div>

        @error('recipient_ids') 
            <div class="mb-4 p-3 rounded-xl bg-red-50 text-red-600 text-xs font-medium border border-red-200 flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ $message }}
            </div> 
        @enderror

        @if ($recipients->isEmpty())
            <x-empty-state title="Belum Ada Penerima Aktif" description="Anda belum menambahkan data penerima MBG yang berstatus aktif. Silakan tambahkan di menu Penerima MBG terlebih dahulu." />
        @else
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-3.5 max-h-[380px] overflow-y-auto p-1.5 border border-slate-200/80 rounded-2xl bg-slate-50/50">
                @foreach ($recipients as $recipient)
                    <label class="relative flex items-start gap-3 p-3.5 rounded-xl border transition-all cursor-pointer select-none"
                           :class="selected.map(Number).includes({{ $recipient->id }}) ? 'bg-white border-emerald-500 shadow-sm ring-1 ring-emerald-500/30' : 'bg-white/80 border-slate-200 hover:border-slate-300'">
                        <div class="flex items-center h-5 mt-0.5">
                            <input type="checkbox" 
                                   name="recipient_ids[]" 
                                   value="{{ $recipient->id }}"
                                   x-model="selected"
                                   :value="{{ $recipient->id }}"
                                   class="w-4 h-4 text-emerald-600 border-slate-300 rounded focus:ring-emerald-500">
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between gap-2">
                                <span class="font-bold text-slate-800 text-sm truncate" title="{{ $recipient->name }}">{{ $recipient->name }}</span>
                                <span class="shrink-0 px-2 py-0.5 rounded-md text-xs font-bold bg-amber-50 text-amber-800 border border-amber-200">
                                    {{ number_format($recipient->portion_count) }} Porsi
                                </span>
                            </div>
                            <div class="text-xs text-slate-500 mt-1 flex items-center gap-1">
                                <svg class="w-3 h-3 text-slate-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                                <span class="truncate">{{ $recipient->location->name ?? 'Tanpa Lokasi' }}</span>
                            </div>
                        </div>
                    </label>
                @endforeach
            </div>
        @endif
    </div>

    <!-- Form Action Buttons -->
    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
        <x-button variant="outline" href="{{ route('distribution-schedules.index') }}">
            Batal
        </x-button>

        <button type="submit" 
                class="inline-flex items-center justify-center gap-2 py-2 px-6 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md transition-all duration-200 disabled:opacity-70 cursor-pointer"
                :disabled="loading || selected.length === 0">
            <span x-show="!loading" class="inline-flex items-center gap-1.5">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                {{ $submitLabel }}
            </span>
            <span x-show="loading" style="display: none;" class="inline-flex items-center gap-2">
                <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                Menyimpan...
            </span>
        </button>
    </div>
</div>
