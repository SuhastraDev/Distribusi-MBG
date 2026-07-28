@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

<x-layouts.app title="Detail Lokasi: {{ $location->name }}" breadcrumb="Data Master / Lokasi & Depot / Detail">
    <div class="max-w-4xl mx-auto space-y-6">
        <!-- Top Navigation Bar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-2xl {{ $location->type === 'depot' ? 'bg-blue-100 text-blue-800' : 'bg-emerald-100 text-emerald-800' }} font-bold flex items-center justify-center text-lg shadow-inner">
                    @if ($location->type === 'depot')
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    @else
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l9-5-9-5-9 5 9 5z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l6.16-3.422a12.083 12.083 0 01.665 6.479A11.952 11.952 0 0012 20.055a11.952 11.952 0 00-6.824-2.998 12.078 12.078 0 01.665-6.479L12 14z"></path></svg>
                    @endif
                </div>
                <div>
                    <div class="flex items-center gap-2">
                        <h2 class="text-xl font-bold text-slate-800">{{ $location->name }}</h2>
                        <x-badge :variant="$location->status">
                            {{ $location->status === 'active' ? 'Aktif' : 'Nonaktif' }}
                        </x-badge>
                    </div>
                    <p class="text-xs text-slate-500 font-mono">Kode: {{ $location->code }} &bull; Tipe: {{ $location->typeLabel() }}</p>
                </div>
            </div>

            <div class="flex items-center gap-2.5 shrink-0">
                <x-button variant="outline" size="sm" href="{{ route('locations.index') }}">
                    &larr; Kembali
                </x-button>
                <x-button variant="primary" size="sm" href="{{ route('locations.edit', $location) }}">
                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                    Edit Lokasi
                </x-button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="md:col-span-2 space-y-6">
                <!-- Map Preview Card -->
                <x-card title="Peta Lokasi & Geografis" subtitle="Visualisasi titik koordinat pada peta OpenStreetMap">
                    <div x-data="{
                        initShowMap() {
                            if (typeof L === 'undefined') return;
                            const mapEl = document.getElementById('map-show');
                            if (!mapEl) return;
                            const lat = {{ (float)$location->latitude }};
                            const lng = {{ (float)$location->longitude }};
                            const map = L.map('map-show').setView([lat, lng], 16);
                            L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
                                maxZoom: 19,
                                attribution: '&copy; OpenStreetMap'
                            }).addTo(map);
                            L.marker([lat, lng]).addTo(map)
                                .bindPopup('<strong>{{ addslashes($location->name) }}</strong><br>{{ $location->typeLabel() }}')
                                .openPopup();
                        }
                    }" x-init="setTimeout(() => initShowMap(), 300)" class="w-full h-72 rounded-xl overflow-hidden border border-slate-200 z-0 bg-slate-100 relative" id="map-show">
                        <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-400">
                            Memuat peta...
                        </div>
                    </div>

                    <div class="mt-4 flex items-center justify-between text-xs text-slate-500 bg-slate-50 p-3 rounded-xl border border-slate-200/60">
                        <div>
                            <span>Latitude: <strong class="text-slate-800 font-mono">{{ $location->latitude }}</strong></span>
                            <span class="mx-2">&bull;</span>
                            <span>Longitude: <strong class="text-slate-800 font-mono">{{ $location->longitude }}</strong></span>
                        </div>
                        <a href="https://www.google.com/maps?q={{ $location->latitude }},{{ $location->longitude }}" target="_blank" class="text-emerald-600 hover:underline font-semibold flex items-center gap-1">
                            Buka di Google Maps &rarr;
                        </a>
                    </div>
                </x-card>

                <!-- Address & Details -->
                <x-card title="Informasi Lengkap" subtitle="Detail fisik dan pengelompokan titik">
                    <dl class="divide-y divide-slate-100 text-sm">
                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Kode Identifier</dt>
                            <dd class="col-span-2 font-semibold text-slate-800 font-mono bg-slate-50 px-2 py-0.5 rounded w-fit">{{ $location->code }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Nama Lokasi / Sekolah</dt>
                            <dd class="col-span-2 font-semibold text-slate-800">{{ $location->name }}</dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Tipe Fasilitas</dt>
                            <dd class="col-span-2">
                                <x-badge :variant="$location->type === 'depot' ? 'depot' : ($location->type === 'school' ? 'sekolah' : 'default')">
                                    {{ $location->typeLabel() }}
                                </x-badge>
                            </dd>
                        </div>

                        <div class="py-3 grid grid-cols-3 gap-4">
                            <dt class="font-medium text-slate-500">Alamat Lengkap</dt>
                            <dd class="col-span-2 text-slate-800 leading-relaxed">{{ $location->address ?? 'Alamat belum dilengkapi' }}</dd>
                        </div>
                    </dl>
                </x-card>
            </div>

            <!-- Sidebar Action Card -->
            <div class="space-y-6">
                <x-card title="Status & Pengawasan" subtitle="Tindakan administratif">
                    <div class="space-y-4 text-sm">
                        <div>
                            <div class="text-xs text-slate-400 font-semibold uppercase">Status Saat Ini</div>
                            <div class="mt-1">
                                <x-badge :variant="$location->status">
                                    {{ $location->status === 'active' ? 'Aktif Digunakan' : 'Dinonaktifkan' }}
                                </x-badge>
                            </div>
                        </div>

                        <div class="text-xs text-slate-500">
                            @if ($location->status === 'active')
                                Lokasi ini dapat dipilih dalam pembuatan jadwal distribusi dan kalkulasi rute Greedy.
                            @else
                                Lokasi ini disembunyikan dari pilihan rute baru namun riwayat pengiriman tetap tersimpan.
                            @endif
                        </div>
                    </div>

                    @if ($location->status === 'active')
                        <x-slot name="footer">
                            <form method="POST" action="{{ route('locations.destroy', $location) }}" class="w-full"
                                  onsubmit="return confirm('Apakah Anda yakin ingin menonaktifkan lokasi {{ addslashes($location->name) }}?');">
                                @csrf
                                @method('DELETE')
                                <x-button type="submit" variant="danger" size="sm" class="w-full justify-center">
                                    <svg class="w-4 h-4 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                    Nonaktifkan Lokasi
                                </x-button>
                            </form>
                        </x-slot>
                    @endif
                </x-card>
            </div>
        </div>
    </div>
</x-layouts.app>

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush
