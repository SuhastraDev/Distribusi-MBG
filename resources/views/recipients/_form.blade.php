@csrf

<div>
    <label for="location_id">Lokasi Aktif</label><br>
    <select id="location_id" name="location_id" required>
        <option value="">Pilih lokasi</option>
        @foreach ($locations as $location)
            <option value="{{ $location->id }}" @selected((int) old('location_id', $recipient->location_id ?? 0) === $location->id)>
                {{ $location->name }} ({{ $location->typeLabel() }})
            </option>
        @endforeach
    </select>
    @error('location_id') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="code">Kode Penerima</label><br>
    <input id="code" name="code" value="{{ old('code', $recipient->code ?? '') }}" required>
    @error('code') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="name">Nama Penerima/Kelompok</label><br>
    <input id="name" name="name" value="{{ old('name', $recipient->name ?? '') }}" required>
    @error('name') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="portion_count">Jumlah Porsi</label><br>
    <input id="portion_count" name="portion_count" type="number" min="1" value="{{ old('portion_count', $recipient->portion_count ?? '') }}" required>
    @error('portion_count') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="notes">Catatan</label><br>
    <textarea id="notes" name="notes">{{ old('notes', $recipient->notes ?? '') }}</textarea>
    @error('notes') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="status">Status</label><br>
    <select id="status" name="status" required>
        <option value="active" @selected(old('status', $recipient->status ?? 'active') === 'active')>Aktif</option>
        <option value="inactive" @selected(old('status', $recipient->status ?? 'active') === 'inactive')>Nonaktif</option>
    </select>
    @error('status') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<button type="submit">{{ $submitLabel }}</button>
