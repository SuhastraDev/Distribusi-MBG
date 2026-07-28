@csrf

<div class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Lokasi Aktif -->
        <div class="md:col-span-2">
            <x-select label="Lokasi Sekolah / Titik Distribusi" name="location_id" required helper="Pilih titik lokasi tempat kelompok penerima atau sekolah ini berada.">
                <option value="">-- Pilih Lokasi Tujuan --</option>
                @foreach ($locations as $location)
                    <option value="{{ $location->id }}" @selected((int) old('location_id', $recipient->location_id ?? 0) === $location->id)>
                        {{ $location->name }} (Tipe: {{ $location->typeLabel() }})
                    </option>
                @endforeach
            </x-select>
        </div>

        <!-- Kode Penerima -->
        <div>
            <x-input 
                label="Kode Penerima" 
                name="code" 
                value="{{ old('code', $recipient->code ?? 'SD-' . strtoupper(substr(uniqid(), -4))) }}" 
                required 
                placeholder="contoh: REC-01 atau SD-01" 
                helper="Kode identifikasi kelompok atau sekolah."
            />
        </div>

        <!-- Status -->
        <div>
            <x-select label="Status Keaktifan" name="status" required>
                <option value="active" @selected(old('status', $recipient->status ?? 'active') === 'active')>Aktif (Menerima Pengiriman MBG)</option>
                <option value="inactive" @selected(old('status', $recipient->status ?? 'active') === 'inactive')>Nonaktif (Sementara Non-aktif)</option>
            </x-select>
        </div>

        <!-- Nama Penerima -->
        <div>
            <x-input 
                label="Nama Sekolah / Kelompok Penerima" 
                name="name" 
                value="{{ old('name', $recipient->name ?? '') }}" 
                required 
                placeholder="contoh: SDN 01 Pagi atau Panti Asuhan Harapan" 
            />
        </div>

        <!-- Jumlah Porsi -->
        <div>
            <x-input 
                label="Jumlah Alokasi Porsi Harian" 
                name="portion_count" 
                type="number" 
                min="1"
                value="{{ old('portion_count', $recipient->portion_count ?? '100') }}" 
                required 
                placeholder="100" 
                helper="Total paket makanan bergizi yang dikirim ke titik ini."
            />
        </div>

        <!-- Catatan -->
        <div class="md:col-span-2">
            <x-textarea 
                label="Catatan Khusus Pengiriman" 
                name="notes" 
                rows="3"
                placeholder="Contoh: Titipkan kepada satpam sekolah atau gerbang depan ditutup pukul 12.00..."
            >{{ old('notes', $recipient->notes ?? '') }}</x-textarea>
        </div>
    </div>

    <!-- Form Action Buttons -->
    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
        <x-button variant="outline" href="{{ route('recipients.index') }}">
            Batal
        </x-button>

        <button type="submit" 
                class="inline-flex items-center justify-center gap-2 py-2 px-5 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md transition-all duration-200 disabled:opacity-70 cursor-pointer"
                :disabled="loading">
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
