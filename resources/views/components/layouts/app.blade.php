@props([
    'title' => null,
    'breadcrumb' => null,
])

<!DOCTYPE html>
<html lang="id" class="h-full bg-slate-50">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ? $title . ' - ' : '' }}{{ config('app.name', 'Distribusi MBG') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="min-h-screen font-sans antialiased text-slate-800 bg-slate-50" x-data="{ sidebarOpen: false }">
    @auth
        <div class="flex min-h-screen w-full bg-slate-50">
            <!-- Mobile Sidebar Backdrop -->
            <div x-show="sidebarOpen" 
                 x-transition:enter="transition-opacity ease-linear duration-300"
                 x-transition:enter-start="opacity-0"
                 x-transition:enter-end="opacity-100"
                 x-transition:leave="transition-opacity ease-linear duration-300"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 class="fixed inset-0 bg-slate-900/60 z-40 lg:hidden backdrop-blur-xs" 
                 @click="sidebarOpen = false" 
                 style="display: none;"></div>

            <!-- Sidebar Navigation -->
            <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full lg:translate-x-0'"
                   class="fixed inset-y-0 left-0 z-50 w-64 shrink-0 bg-slate-900 text-slate-300 transition-transform duration-300 ease-in-out flex flex-col justify-between border-r border-slate-800 shadow-xl lg:shadow-none">
            
            <!-- Sidebar Header -->
            <div class="flex flex-col grow overflow-y-auto">
                <div class="flex items-center gap-3 px-6 h-16 shrink-0 bg-slate-950/50 border-b border-slate-800/80">
                    <div class="w-9 h-9 rounded-xl bg-emerald-600 flex items-center justify-center text-white shadow-md shadow-emerald-900/20 font-bold text-lg">
                        M
                    </div>
                    <div>
                        <span class="font-bold text-white text-base tracking-tight block">Distribusi MBG</span>
                        <span class="text-xs text-emerald-400 font-medium">Sistem Logistik Pangan</span>
                    </div>
                </div>

                <!-- Role Badge Display in Sidebar -->
                <div class="px-6 py-4 border-b border-slate-800/60">
                    <div class="text-xs text-slate-400 mb-1">Akses Peran:</div>
                    <div class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md bg-slate-800/80 border border-slate-700/60 text-xs font-semibold text-emerald-400 w-full">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                        {{ auth()->user()->role?->display_name ?? 'Pengguna' }}
                    </div>
                </div>

                <!-- Sidebar Menu Items -->
                <nav class="flex-1 px-4 py-6 space-y-1">
                    @if (auth()->user()->hasRole('admin'))
                        <!-- ADMIN MENU -->
                        <div class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu Utama</div>
                        <a href="{{ route('admin.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('admin.dashboard', 'dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard Admin
                        </a>

                        <div class="pt-4 pb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Data Master</div>
                        <a href="{{ route('officers.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('officers.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            Petugas Distribusi
                        </a>
                        <a href="{{ route('locations.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('locations.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                            Lokasi & Depot
                        </a>
                        <a href="{{ route('recipients.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('recipients.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            Penerima MBG
                        </a>

                        <div class="pt-4 pb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Logistik & Jadwal</div>
                        <a href="{{ route('distribution-schedules.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('distribution-schedules.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Jadwal Distribusi
                        </a>
                        <a href="{{ route('distribution-runs.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('distribution-runs.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Data Distribusi (Run)
                        </a>
                        <a href="{{ route('route-plans.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('route-plans.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                            Monitoring & Rute
                        </a>

                        <div class="pt-4 pb-2 px-3 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Analisis & Laporan</div>
                        <a href="{{ route('reports.distributions.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Laporan Distribusi
                        </a>

                    @elseif (auth()->user()->hasRole('petugas'))
                        <!-- PETUGAS MENU -->
                        <div class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu Lapangan</div>
                        <a href="{{ route('officer.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('officer.dashboard', 'dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard Petugas
                        </a>
                        <a href="{{ route('distribution-runs.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('distribution-runs.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                            Tugas Distribusi
                        </a>
                        <a href="{{ route('route-plans.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('route-plans.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                            Rute & Monitoring
                        </a>

                    @elseif (auth()->user()->hasRole('kepala_sppg'))
                        <!-- KEPALA SPPG MENU -->
                        <div class="px-3 mb-2 text-[11px] font-semibold uppercase tracking-wider text-slate-500">Menu Pengawasan</div>
                        <a href="{{ route('head.dashboard') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('head.dashboard', 'dashboard') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                            Dashboard Kepala SPPG
                        </a>
                        <a href="{{ route('route-plans.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('route-plans.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-54.424a1 1 0 01-.866-.5L2.5 15l4.5-9 6 12 5-10 6 12zm0 0V4"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l6-12 5 10"></path></svg>
                            Monitoring Realtime
                        </a>
                        <a href="{{ route('reports.distributions.index') }}" 
                           class="flex items-center gap-3 px-3 py-2.5 rounded-xl text-sm font-medium transition-all {{ request()->routeIs('reports.*') ? 'bg-emerald-600 text-white shadow-md shadow-emerald-900/30 font-semibold' : 'text-slate-300 hover:bg-slate-800/60 hover:text-white' }}">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                            Laporan Logistik
                        </a>
                    @endif
                </nav>

                <!-- Sidebar User Profile Footer -->
                <div class="p-4 border-t border-slate-800/80 bg-slate-950/30">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-9 h-9 rounded-full bg-slate-800 border border-slate-700 flex items-center justify-center text-sm font-bold text-emerald-400 shrink-0 uppercase">
                                {{ substr(auth()->user()->name, 0, 2) }}
                            </div>
                            <div class="min-w-0">
                                <p class="text-sm font-semibold text-white truncate" title="{{ auth()->user()->name }}">
                                    {{ auth()->user()->name }}
                                </p>
                                <p class="text-xs text-slate-400 truncate" title="{{ auth()->user()->email }}">
                                    {{ auth()->user()->email }}
                                </p>
                            </div>
                        </div>

                        <!-- Logout Form Button -->
                        <form method="POST" action="{{ route('logout') }}">
                            @csrf
                            <button type="submit" 
                                    title="Keluar dari sistem"
                                    class="p-2 rounded-lg text-slate-400 hover:text-red-400 hover:bg-slate-800/60 transition-colors cursor-pointer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Wrapper -->
        <div class="flex-1 flex flex-col min-w-0 overflow-x-hidden lg:pl-64">
            <!-- Topbar -->
            <header class="h-16 bg-white border-b border-slate-200/80 sticky top-0 z-30 px-4 sm:px-6 lg:px-8 flex items-center justify-between shadow-2xs">
                <div class="flex items-center gap-4">
                    <!-- Mobile Sidebar Hamburger -->
                    <button @click="sidebarOpen = true" type="button" class="lg:hidden p-2 -ml-2 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-slate-100 focus:outline-none">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                    </button>

                    <!-- Breadcrumbs / Page Title -->
                    <div>
                        @if ($breadcrumb)
                            <div class="text-xs font-medium text-slate-400 flex items-center gap-1.5 mb-0.5">
                                {{ $breadcrumb }}
                            </div>
                        @endif
                        <h1 class="text-lg font-bold text-slate-800 leading-tight">
                            {{ $title ?? 'Dashboard' }}
                        </h1>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <span class="hidden sm:inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-medium bg-emerald-50 text-emerald-700 border border-emerald-200/60">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-500"></span>
                        Aktif
                    </span>
                </div>
            </header>

            <!-- Main Content Area -->
            <main class="flex-1 p-4 sm:p-6 lg:p-8 max-w-7xl mx-auto w-full">
                <!-- Session Alerts -->
                @if (session('status') || session('success'))
                    <div class="mb-6">
                        <x-alert variant="success" title="Berhasil">
                            {{ session('status') ?? session('success') }}
                        </x-alert>
                    </div>
                @endif

                @if (session('error') || session('danger'))
                    <div class="mb-6">
                        <x-alert variant="error" title="Perhatian">
                            {{ session('error') ?? session('danger') }}
                        </x-alert>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="mb-6">
                        <x-alert variant="error" title="Terdapat Kesalahan Input">
                            <ul class="list-disc list-inside space-y-1 text-xs mt-1">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </x-alert>
                    </div>
                @endif

                <!-- Content Slot -->
                {{ $slot }}
            </main>

            <!-- Footer -->
            <footer class="py-4 px-6 border-t border-slate-200 bg-white text-center text-xs text-slate-500">
                &copy; {{ date('Y') }} Sistem Distribusi Makan Bergizi Gratis (MBG). All rights reserved.
            </footer>
        </div>
        </div>
    @else
        <!-- Guest Layout (For Login etc) -->
        <div class="min-h-screen flex flex-col justify-center py-12 sm:px-6 lg:px-8 bg-gradient-to-br from-slate-900 via-slate-800 to-emerald-950">
            {{ $slot }}
        </div>
    @endauth

    @stack('scripts')
</body>
</html>
