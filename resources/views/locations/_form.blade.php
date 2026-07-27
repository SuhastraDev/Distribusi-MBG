@csrf

<div>
    <label for="code">Kode Lokasi</label><br>
    <input id="code" name="code" value="{{ old('code', $location->code ?? '') }}" required>
    @error('code') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="name">Nama Lokasi</label><br>
    <input id="name" name="name" value="{{ old('name', $location->name ?? '') }}" required>
    @error('name') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="type">Tipe Lokasi</label><br>
    <select id="type" name="type" required>
        <option value="depot" @selected(old('type', $location->type ?? 'school') === 'depot')>Depot</option>
        <option value="school" @selected(old('type', $location->type ?? 'school') === 'school')>Sekolah</option>
        <option value="other" @selected(old('type', $location->type ?? 'school') === 'other')>Lainnya</option>
    </select>
    @error('type') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="address">Alamat</label><br>
    <textarea id="address" name="address">{{ old('address', $location->address ?? '') }}</textarea>
    @error('address') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="latitude">Latitude</label><br>
    <input id="latitude" name="latitude" value="{{ old('latitude', $location->latitude ?? '') }}" required>
    @error('latitude') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="longitude">Longitude</label><br>
    <input id="longitude" name="longitude" value="{{ old('longitude', $location->longitude ?? '') }}" required>
    @error('longitude') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="status">Status</label><br>
    <select id="status" name="status" required>
        <option value="active" @selected(old('status', $location->status ?? 'active') === 'active')>Aktif</option>
        <option value="inactive" @selected(old('status', $location->status ?? 'active') === 'inactive')>Nonaktif</option>
    </select>
    @error('status') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<button type="submit">{{ $submitLabel }}</button>
