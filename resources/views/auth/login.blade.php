<x-layouts.app title="Login Sistem">
    <div class="sm:mx-auto sm:w-full sm:max-w-md">
        <div class="flex justify-center">
            <div class="w-14 h-14 rounded-2xl bg-emerald-600 flex items-center justify-center text-white shadow-lg shadow-emerald-500/30 font-extrabold text-2xl border border-emerald-400/30">
                M
            </div>
        </div>
        <h2 class="mt-6 text-center text-2xl font-bold tracking-tight text-white">
            Sistem Distribusi MBG <span class="sr-only">Login Sistem Distribusi MBG</span>
        </h2>
        <p class="mt-2 text-center text-sm text-slate-400 max-w-sm mx-auto">
            Platform Manajemen Logistik & Monitoring Makan Bergizi Gratis
        </p>
    </div>

    <div class="mt-8 sm:mx-auto sm:w-full sm:max-w-md px-4 sm:px-0">
        <div class="bg-white py-8 px-6 shadow-2xl sm:rounded-2xl sm:px-10 border border-slate-100">
            <div class="mb-6 pb-4 border-b border-slate-100 text-center">
                <h3 class="text-base font-semibold text-slate-800">Silakan Masuk</h3>
                <p class="text-xs text-slate-500 mt-0.5">Gunakan akun Admin, Petugas, atau Kepala SPPG</p>
            </div>

            @if ($errors->any())
                <div class="mb-6">
                    <x-alert variant="error" title="Login Gagal" :dismissible="false">
                        {{ $errors->first() }}
                    </x-alert>
                </div>
            @endif

            <form
                class="space-y-5"
                action="{{ route('login.store') }}"
                method="POST"
                x-data="{
                    loading: false,
                    demoPassword: 'password',
                    demoAccounts: [
                        { role: 'Admin', email: 'admin@distribusimbg.test', tone: 'emerald', description: 'Kelola data & rute' },
                        { role: 'Kepala SPPG', email: 'kepala@distribusimbg.test', tone: 'blue', description: 'Monitoring & laporan' },
                        { role: 'Petugas', email: 'petugas@distribusimbg.test', tone: 'amber', description: 'Update distribusi' },
                    ],
                    fillDemo(account) {
                        this.$refs.email.value = account.email;
                        this.$refs.password.value = this.demoPassword;
                        this.$refs.email.dispatchEvent(new Event('input', { bubbles: true }));
                        this.$refs.password.dispatchEvent(new Event('input', { bubbles: true }));
                        this.$refs.email.focus();
                    },
                }"
                @submit="loading = true"
            >
                @csrf

                <div class="rounded-2xl border border-dashed border-emerald-200 bg-emerald-50/60 p-4">
                    <div class="flex items-start gap-3">
                        <div class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-emerald-600 text-white shadow-sm">
                            <svg class="h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7h3m0 0v3m0-3-5 5m-3-3H6a2 2 0 0 0-2 2v7a2 2 0 0 0 2 2h7a2 2 0 0 0 2-2v-4"></path>
                            </svg>
                        </div>
                        <div>
                            <p class="text-sm font-semibold text-slate-800">Shortcut akun demo</p>
                            <p class="mt-0.5 text-xs leading-5 text-slate-500">Klik salah satu role untuk mengisi email dan password otomatis.</p>
                        </div>
                    </div>

                    <div class="mt-4 grid gap-2">
                        <template x-for="account in demoAccounts" :key="account.email">
                            <button
                                type="button"
                                class="group flex w-full items-center justify-between rounded-xl border border-white/80 bg-white px-3 py-2.5 text-left shadow-sm transition hover:-translate-y-0.5 hover:border-emerald-200 hover:shadow-md focus:outline-none focus:ring-2 focus:ring-emerald-500 focus:ring-offset-2"
                                @click="fillDemo(account)"
                            >
                                <span>
                                    <span class="block text-sm font-semibold text-slate-800" x-text="account.role"></span>
                                    <span class="block text-xs text-slate-500" x-text="account.description"></span>
                                </span>
                                <span class="rounded-lg bg-slate-100 px-2 py-1 text-[11px] font-semibold text-slate-500 transition group-hover:bg-emerald-100 group-hover:text-emerald-700">
                                    Pakai akun
                                </span>
                            </button>
                        </template>
                    </div>
                </div>

                <div>
                    <x-input 
                        label="Alamat Email / Username" 
                        name="email" 
                        type="email" 
                        value="{{ old('email') }}" 
                        required 
                        autofocus 
                        placeholder="contoh: admin@mbg.id" 
                        autocomplete="email"
                        x-ref="email"
                    />
                </div>

                <div>
                    <x-input 
                        label="Password" 
                        name="password" 
                        type="password" 
                        required 
                        placeholder="••••••••" 
                        autocomplete="current-password"
                        x-ref="password"
                    />
                </div>

                <div class="flex items-center justify-between">
                    <div class="flex items-center">
                        <input id="remember" name="remember" type="checkbox" value="1" 
                            class="h-4 w-4 rounded border-slate-300 text-emerald-600 focus:ring-emerald-500 transition-colors cursor-pointer">
                        <label for="remember" class="ml-2 block text-xs font-medium text-slate-600 cursor-pointer">
                            Ingat sesi saya
                        </label>
                    </div>
                </div>

                <div class="pt-2">
                    <button type="submit" 
                            class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 border border-transparent rounded-xl shadow-md text-sm font-semibold text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition-all duration-200 cursor-pointer disabled:opacity-70"
                            :disabled="loading">
                        <span x-show="!loading" class="inline-flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"></path></svg>
                            Masuk ke Sistem
                        </span>
                        <span x-show="loading" style="display: none;" class="inline-flex items-center gap-2">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            Memverifikasi...
                        </span>
                    </button>
                </div>
            </form>
        </div>

        <div class="mt-6 text-center text-xs text-slate-400">
            <p>Sistem Distribusi MBG &bull; Skripsi & Pengawasan Realtime</p>
        </div>
    </div>
</x-layouts.app>
