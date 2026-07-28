@props([
    'id' => 'map-' . substr(md5(uniqid()), 0, 6),
    'height' => '420px',
    'lat' => -6.200000,
    'lng' => 106.816666,
    'zoom' => 13,
    'markers' => [], // Array of ['lat' => ..., 'lng' => ..., 'title' => ..., 'popup' => ..., 'type' => 'default|depot|destination']
    'polyline' => [], // Array of [lat, lng] coordinates
    'officer' => null, // ['lat' => ..., 'lng' => ..., 'popup' => ..., 'accuracy' => ...]
    'fitBounds' => true,
])

@pushOnce('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin="">
@endPushOnce

@pushOnce('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endPushOnce

<div class="relative w-full rounded-2xl overflow-hidden border border-slate-200/80 shadow-sm bg-slate-100" style="height: {{ $height }};">
    <div id="{{ $id }}" class="w-full h-full z-10"></div>
    
    <!-- Loading / Overlay placeholder -->
    <div id="{{ $id }}-loader" class="absolute inset-0 bg-slate-900/10 backdrop-blur-[1px] z-20 flex items-center justify-center pointer-events-none transition-opacity duration-300">
        <div class="bg-white px-4 py-2 rounded-xl shadow-md border border-slate-200 flex items-center gap-2 text-xs font-semibold text-slate-700">
            <svg class="animate-spin h-4 w-4 text-emerald-600" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
            Memuat Peta OpenStreetMap...
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() {
            const mapEl = document.getElementById('{{ $id }}');
            const loaderEl = document.getElementById('{{ $id }}-loader');
            if (!mapEl || typeof L === 'undefined') return;

            const map = L.map('{{ $id }}').setView([{{ $lat }}, {{ $lng }}], {{ $zoom }});

            L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                maxZoom: 19,
                attribution: '&copy; <a href="https://www.openstreetmap.org/copyright">OpenStreetMap</a> contributors'
            }).addTo(map);

            const markersData = @json($markers);
            const polylineData = @json($polyline);
            const officerPosition = @json($officer);
            const officerData = officerPosition;
            const bounds = [];

            // Custom Icons
            const depotIcon = L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="background-color: #4f46e5; width: 28px; height: 28px; border-radius: 50%; border: 3px solid white; box-shadow: 0 2px 5px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 12px;">D</div>`,
                iconSize: [28, 28],
                iconAnchor: [14, 14]
            });

            const destinationIcon = (order) => L.divIcon({
                className: 'custom-map-marker',
                html: `<div style="background-color: #10b981; width: 26px; height: 26px; border-radius: 50%; border: 2px solid white; box-shadow: 0 2px 4px rgba(0,0,0,0.3); display: flex; align-items: center; justify-content: center; color: white; font-weight: bold; font-size: 11px;">${order || 'T'}</div>`,
                iconSize: [26, 26],
                iconAnchor: [13, 13]
            });

            // Add Markers
            if (markersData && markersData.length > 0) {
                markersData.forEach((item, index) => {
                    if (!item.lat || !item.lng) return;
                    bounds.push([item.lat, item.lng]);
                    
                    let icon = null;
                    if (item.type === 'depot' || item.type === 'start') icon = depotIcon;
                    else if (item.type === 'destination') icon = destinationIcon(item.order || (index));

                    const markerOpts = icon ? { icon: icon } : {};
                    const marker = L.marker([item.lat, item.lng], markerOpts).addTo(map);

                    if (item.popup) {
                        marker.bindPopup(item.popup);
                    } else if (item.title) {
                        marker.bindPopup(`<strong>${item.title}</strong>`);
                    }
                });
            }

            // Add Polyline (Route)
            if (polylineData && polylineData.length > 1) {
                polylineData.forEach(coord => bounds.push(coord));
                
                // Draw actual road route using OSRM
                const coordinatesString = polylineData.map(c => c[1] + ',' + c[0]).join(';');
                fetch(`https://router.project-osrm.org/route/v1/driving/${coordinatesString}?geometries=geojson&overview=full`)
                    .then(res => res.json())
                    .then(data => {
                        if (data && data.routes && data.routes.length > 0) {
                            // OSRM geometry is [longitude, latitude], Leaflet needs [latitude, longitude]
                            const routeGeometry = data.routes[0].geometry.coordinates.map(c => [c[1], c[0]]);
                            L.polyline(routeGeometry, {
                                color: '#2563eb',
                                weight: 5,
                                opacity: 0.8,
                                smoothFactor: 1
                            }).addTo(map);
                        } else {
                            // Fallback to straight lines
                            L.polyline(polylineData, { color: '#64748b', weight: 4, opacity: 0.8, dashArray: '5, 10' }).addTo(map);
                        }
                    })
                    .catch(err => {
                        console.error('OSRM Routing Error:', err);
                        // Fallback to straight lines on error
                        L.polyline(polylineData, { color: '#64748b', weight: 4, opacity: 0.8, dashArray: '5, 10' }).addTo(map);
                    });
            }

            // Add Officer Live GPS Position
            if (officerData && officerData.lat && officerData.lng) {
                bounds.push([officerData.lat, officerData.lng]);
                
                // Pulsing Officer Circle Marker
                L.circleMarker([officerData.lat, officerData.lng], {
                    radius: 10,
                    color: '#dc2626',
                    fillColor: '#ef4444',
                    fillOpacity: 0.9,
                    weight: 3
                }).addTo(map).bindPopup(officerData.popup || '<strong>Posisi Petugas Lapangan</strong>');

                // Accuracy Circle
                if (officerData.accuracy && officerData.accuracy > 0) {
                    L.circle([officerData.lat, officerData.lng], {
                        radius: officerData.accuracy,
                        color: '#ef4444',
                        fillColor: '#f87171',
                        fillOpacity: 0.15,
                        weight: 1
                    }).addTo(map);
                }
            }

            // Fit bounds if needed
            if ({{ $fitBounds ? 'true' : 'false' }} && bounds.length > 0) {
                if (bounds.length === 1) {
                    map.setView(bounds[0], 15);
                } else {
                    map.fitBounds(bounds, { padding: [40, 40] });
                }
            }

            // Hide loader
            if (loaderEl) {
                loaderEl.style.opacity = '0';
                setTimeout(() => loaderEl.remove(), 300);
            }

            // Invalidate size to prevent tile clipping
            setTimeout(() => map.invalidateSize(), 400);
        }, 300);
    });
</script>
@endpush
