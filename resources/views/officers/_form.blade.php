@csrf

<div class="space-y-6" x-data="{ loading: false }" @submit="loading = true">
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kode Petugas -->
        <div>
            <x-input 
                label="Kode Petugas" 
                name="officer_code" 
                value="{{ old('officer_code', $officer->officer_code ?? 'PTG-' . strtoupper(substr(uniqid(), -4))) }}" 
                required 
                placeholder="contoh: PTG-001" 
                helper="Kode unik identifier petugas di lapangan."
            />
        </div>

        <!-- Status -->
        <div>
            <x-select label="Status Keaktifan" name="status" required>
                <option value="active" @selected(old('status', $officer->status ?? 'active') === 'active')>Aktif (Siap Bertugas)</option>
                <option value="inactive" @selected(old('status', $officer->status ?? 'active') === 'inactive')>Nonaktif (Cuti / Non-aktif)</option>
            </x-select>
        </div>

        <!-- Nama Petugas -->
        <div>
            <x-input 
                label="Nama Lengkap Personel" 
                name="name" 
                value="{{ old('name', $officer->name ?? '') }}" 
                required 
                placeholder="Masukkan nama lengkap petugas..." 
            />
        </div>

        <!-- Email Login -->
        <div>
            <x-input 
                label="Email Login (Akun User)" 
                name="email" 
                type="email"
                value="{{ old('email', $officer->user->email ?? '') }}" 
                required 
                placeholder="contoh: petugas01@mbg.id" 
                helper="Digunakan sebagai kredensial masuk ke aplikasi web/mobile."
            />
        </div>

        <!-- Nomor HP -->
        <div>
            <x-input 
                label="Nomor Telepon / WhatsApp" 
                name="phone" 
                type="tel"
                value="{{ old('phone', $officer->phone ?? '') }}" 
                placeholder="08xxxxxxxxxx" 
            />
        </div>

        <!-- Alamat -->
        <div class="md:col-span-2">
            <x-textarea 
                label="Alamat Domisili" 
                name="address" 
                rows="3"
                placeholder="Alamat lengkap tempat tinggal petugas..."
            >{{ old('address', $officer->address ?? '') }}</x-textarea>
        </div>
    </div>

    <!-- Security / Password Section -->
    <div class="pt-4 border-t border-slate-200">
        <h4 class="text-sm font-bold text-slate-800 mb-1">Pengaturan Kredensial Keamanan</h4>
        <p class="text-xs text-slate-500 mb-4">
            {{ isset($officer) ? 'Kosongkan jika tidak ingin mengubah password akun petugas.' : 'Buat password untuk akses login petugas ke sistem.' }}
        </p>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <x-input 
                    label="Password {{ isset($officer) ? '(Opsional)' : '' }}" 
                    name="password" 
                    type="password" 
                    :required="!isset($officer)" 
                    placeholder="••••••••" 
                />
            </div>

            <div>
                <x-input 
                    label="Konfirmasi Password" 
                    name="password_confirmation" 
                    type="password" 
                    :required="!isset($officer)" 
                    placeholder="••••••••" 
                />
            </div>
        </div>
    </div>

    <!-- Form Action Buttons -->
    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
        <x-button variant="outline" href="{{ route('officers.index') }}">
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
