@csrf

@push('styles')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="" />
@endpush

<div class="space-y-6" x-data="{ 
    loading: false,
    lat: '{{ old('latitude', $location->latitude ?? '-6.200000') }}',
    lng: '{{ old('longitude', $location->longitude ?? '106.816666') }}',
    initMap() {
        if (typeof L === 'undefined') return;
        const mapEl = document.getElementById('map-preview');
        if (!mapEl) return;
        
        const initialLat = parseFloat(this.lat) || -6.200000;
        const initialLng = parseFloat(this.lng) || 106.816666;
        
        const map = L.map('map-preview').setView([initialLat, initialLng], 14);
        L.tileLayer('https://tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap'
        }).addTo(map);
        
        let marker = L.marker([initialLat, initialLng], { draggable: true }).addTo(map);
        
        marker.on('dragend', (e) => {
            const pos = marker.getLatLng();
            this.lat = pos.lat.toFixed(6);
            this.lng = pos.lng.toFixed(6);
        });

        map.on('click', (e) => {
            marker.setLatLng(e.latlng);
            this.lat = e.latlng.lat.toFixed(6);
            this.lng = e.latlng.lng.toFixed(6);
        });

        this.$watch('lat', val => {
            const num = parseFloat(val);
            if (!isNaN(num) && marker) { marker.setLatLng([num, parseFloat(this.lng) || 0]); map.panTo([num, parseFloat(this.lng) || 0]); }
        });
        this.$watch('lng', val => {
            const num = parseFloat(val);
            if (!isNaN(num) && marker) { marker.setLatLng([parseFloat(this.lat) || 0, num]); map.panTo([parseFloat(this.lat) || 0, num]); }
        });
    }
}" x-init="setTimeout(() => initMap(), 300)" @submit="loading = true">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        <!-- Kode Lokasi -->
        <div>
            <x-input 
                label="Kode Lokasi" 
                name="code" 
                value="{{ old('code', $location->code ?? 'LOK-' . strtoupper(substr(uniqid(), -4))) }}" 
                required 
                placeholder="contoh: DEPOT-01 atau SD-01" 
                helper="Identifier unik titik depot masakan atau sekolah."
            />
        </div>

        <!-- Tipe Lokasi -->
        <div>
            <x-select label="Tipe Lokasi" name="type" required>
                <option value="depot" @selected(old('type', $location->type ?? 'school') === 'depot')>Depot Masakan (Dapur Pusat / Kitchen)</option>
                <option value="school" @selected(old('type', $location->type ?? 'school') === 'school')>Sekolah Penerima (Titik Kirim)</option>
                <option value="puskesmas" @selected(old('type', $location->type ?? 'school') === 'puskesmas')>Puskesmas Penerima (Titik Kirim)</option>
                <option value="other" @selected(old('type', $location->type ?? 'school') === 'other')>Lainnya (Kantor / Gudang)</option>
            </x-select>
        </div>

        <!-- Nama Lokasi -->
        <div>
            <x-input 
                label="Nama Lokasi / Sekolah" 
                name="name" 
                value="{{ old('name', $location->name ?? '') }}" 
                required 
                placeholder="contoh: SDN 01 Pagi atau Depot Pusat Selatan" 
            />
        </div>

        <!-- Status -->
        <div>
            <x-select label="Status Operasional" name="status" required>
                <option value="active" @selected(old('status', $location->status ?? 'active') === 'active')>Aktif (Digunakan dalam Rute)</option>
                <option value="inactive" @selected(old('status', $location->status ?? 'active') === 'inactive')>Nonaktif (Sementara Non-aktif)</option>
            </x-select>
        </div>

        <!-- Alamat -->
        <div class="md:col-span-2">
            <x-textarea 
                label="Alamat Lengkap" 
                name="address" 
                rows="2"
                placeholder="Alamat fisik lokasi depot atau sekolah..."
            >{{ old('address', $location->address ?? '') }}</x-textarea>
        </div>
    </div>

    <!-- GPS Coordinates & Map Picker -->
    <div class="pt-4 border-t border-slate-200">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-3">
            <div>
                <h4 class="text-sm font-bold text-slate-800">Koordinat Geografis (GPS)</h4>
                <p class="text-xs text-slate-500">Klik atau geser pin pada peta untuk menentukan posisi Latitude dan Longitude secara presisi.</p>
            </div>
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span> Peta Interaktif
            </span>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-4">
            <div>
                <x-input 
                    label="Latitude (Garis Lintang)" 
                    name="latitude" 
                    x-model="lat"
                    required 
                    placeholder="-6.200000" 
                />
            </div>

            <div>
                <x-input 
                    label="Longitude (Garis Bujur)" 
                    name="longitude" 
                    x-model="lng"
                    required 
                    placeholder="106.816666" 
                />
            </div>
        </div>

        <!-- Map Preview Container -->
        <div class="w-full h-64 sm:h-80 rounded-xl overflow-hidden border-2 border-slate-200 shadow-inner relative bg-slate-100 z-0" id="map-preview">
            <div class="absolute inset-0 flex items-center justify-center text-xs text-slate-400">
                Memuat peta interaktif OpenStreetMap...
            </div>
        </div>
    </div>

    <!-- Form Action Buttons -->
    <div class="pt-6 border-t border-slate-200 flex items-center justify-end gap-3">
        <x-button variant="outline" href="{{ route('locations.index') }}">
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

@push('scripts')
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush
