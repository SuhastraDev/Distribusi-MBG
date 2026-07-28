<x-layouts.app title="Ubah Password & Profil Akun" breadcrumb="Akun Saya / Keamanan & Password">
    <div class="max-w-4xl mx-auto space-y-6">
        
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Keamanan & Profil Akun</h2>
                <p class="text-sm text-slate-500">Kelola kredensial login dan lihat informasi identitas akun Anda di dalam sistem</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            
            <!-- User Profile Summary Column -->
            <div class="md:col-span-1 space-y-4">
                <x-card title="Identitas Akun">
                    <div class="flex flex-col items-center text-center p-4">
                        <div class="w-20 h-20 rounded-full bg-emerald-600 text-white font-bold text-2xl flex items-center justify-center shadow-lg shadow-emerald-900/20 mb-3">
                            {{ substr(auth()->user()->name ?? 'U', 0, 1) }}
                        </div>
                        <h3 class="text-base font-bold text-slate-800">{{ auth()->user()->name }}</h3>
                        <p class="text-xs text-slate-500 mb-3">{{ auth()->user()->email }}</p>
                        
                        <div class="w-full pt-3 border-t border-slate-100 space-y-2 text-left text-xs">
                            <div class="flex justify-between py-1">
                                <span class="text-slate-400">Peran Akun:</span>
                                <span class="font-bold text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded border border-emerald-200">
                                    {{ auth()->user()->role?->display_name ?? auth()->user()->role?->name ?? 'Pengguna' }}
                                </span>
                            </div>
                            @if (auth()->user()->officer)
                                <div class="flex justify-between py-1">
                                    <span class="text-slate-400">Kode Petugas:</span>
                                    <span class="font-mono font-bold text-slate-700">{{ auth()->user()->officer->code }}</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-slate-400">No. Telepon:</span>
                                    <span class="font-medium text-slate-700">{{ auth()->user()->officer->phone ?: '-' }}</span>
                                </div>
                                <div class="flex justify-between py-1">
                                    <span class="text-slate-400">Kapasitas Porsi:</span>
                                    <span class="font-medium text-slate-700">{{ number_format(auth()->user()->officer->max_portion_capacity) }} porsi</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </x-card>

                <div class="bg-amber-50 p-4 rounded-2xl border border-amber-200/80 text-amber-900 text-xs space-y-1">
                    <div class="font-bold flex items-center gap-1.5">
                        <svg class="w-4 h-4 text-amber-600 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                        Rekomendasi Keamanan
                    </div>
                    <p class="text-[11px] leading-relaxed text-amber-800">
                        Gunakan kombinasi minimal 8 karakter dengan angka atau simbol agar akun Anda tetap aman dari akses tidak sah.
                    </p>
                </div>
            </div>

            <!-- Change Password Form Column -->
            <div class="md:col-span-2">
                <x-card title="Ubah Password Login" subtitle="Perbarui kata sandi Anda secara berkala untuk menjaga keamanan data">
                    <form method="POST" action="{{ route('password.update') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
                        @csrf
                        @method('PUT')

                        <div>
                            <x-input 
                                label="Password Saat Ini (Old Password)" 
                                name="current_password" 
                                type="password" 
                                required 
                                placeholder="Masukkan password Anda saat ini..."
                                autocomplete="current-password"
                            />
                        </div>

                        <div class="pt-2 border-t border-slate-100"></div>

                        <div>
                            <x-input 
                                label="Password Baru (New Password)" 
                                name="password" 
                                type="password" 
                                required 
                                placeholder="Minimal 8 karakter..."
                                autocomplete="new-password"
                            />
                        </div>

                        <div>
                            <x-input 
                                label="Konfirmasi Password Baru" 
                                name="password_confirmation" 
                                type="password" 
                                required 
                                placeholder="Ketik ulang password baru..."
                                autocomplete="new-password"
                            />
                        </div>

                        <div class="pt-4 border-t border-slate-100 flex items-center justify-end gap-3">
                            <button type="submit" 
                                    class="inline-flex items-center justify-center gap-2 py-2 px-6 rounded-xl text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 shadow-md transition-all duration-200 disabled:opacity-70 cursor-pointer"
                                    :disabled="loading">
                                <span x-show="!loading" class="inline-flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V9a2 2 0 00-2-2h-3m-1 4l-3 3m0 0l-3-3m3 3V4"></path></svg>
                                    Simpan Password Baru
                                </span>
                                <span x-show="loading" style="display: none;" class="inline-flex items-center gap-2">
                                    <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Memperbarui...
                                </span>
                            </button>
                        </div>
                    </form>
                </x-card>
            </div>

        </div>

    </div>
</x-layouts.app>
