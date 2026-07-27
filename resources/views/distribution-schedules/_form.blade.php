@csrf

@php
    $selectedRecipientIds = collect(old(
        'recipient_ids',
        isset($distributionSchedule)
            ? $distributionSchedule->destinations->pluck('recipient_id')->all()
            : []
    ))->map(fn ($id) => (int) $id)->all();
@endphp

<div>
    <label for="code">Kode Jadwal</label><br>
    <input id="code" name="code" value="{{ old('code', $distributionSchedule->code ?? '') }}" required>
    @error('code') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="scheduled_date">Tanggal Distribusi</label><br>
    <input id="scheduled_date" name="scheduled_date" type="date" value="{{ old('scheduled_date', isset($distributionSchedule) ? $distributionSchedule->scheduled_date->format('Y-m-d') : now()->toDateString()) }}" required>
    @error('scheduled_date') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="officer_id">Petugas Distribusi</label><br>
    <select id="officer_id" name="officer_id" required>
        <option value="">Pilih petugas</option>
        @foreach ($officers as $officer)
            <option value="{{ $officer->id }}" @selected((int) old('officer_id', $distributionSchedule->officer_id ?? 0) === $officer->id)>
                {{ $officer->name }}
            </option>
        @endforeach
    </select>
    @error('officer_id') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="depot_location_id">Depot Awal</label><br>
    <select id="depot_location_id" name="depot_location_id" required>
        <option value="">Pilih depot</option>
        @foreach ($depots as $depot)
            <option value="{{ $depot->id }}" @selected((int) old('depot_location_id', $distributionSchedule->depot_location_id ?? 0) === $depot->id)>
                {{ $depot->name }}
            </option>
        @endforeach
    </select>
    @error('depot_location_id') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<fieldset>
    <legend>Tujuan Distribusi</legend>
    <p>Pilih minimal satu penerima aktif. Total porsi otomatis dihitung dari pilihan ini.</p>
    @error('recipient_ids') <p style="color: #dc2626;">{{ $message }}</p> @enderror

    @forelse ($recipients as $recipient)
        <label style="display: block; margin-bottom: 8px;">
            <input
                type="checkbox"
                name="recipient_ids[]"
                value="{{ $recipient->id }}"
                @checked(in_array($recipient->id, $selectedRecipientIds, true))
            >
            {{ $recipient->name }} - {{ $recipient->location->name }} ({{ $recipient->portion_count }} porsi)
        </label>
    @empty
        <p>Belum ada penerima aktif yang bisa dipilih.</p>
    @endforelse
</fieldset>

<div>
    <label for="status">Status Jadwal</label><br>
    <select id="status" name="status" required>
        <option value="draft" @selected(old('status', $distributionSchedule->status ?? 'draft') === 'draft')>Draft</option>
        <option value="scheduled" @selected(old('status', $distributionSchedule->status ?? 'draft') === 'scheduled')>Terjadwal</option>
        <option value="cancelled" @selected(old('status', $distributionSchedule->status ?? 'draft') === 'cancelled')>Dibatalkan</option>
    </select>
    @error('status') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<div>
    <label for="notes">Catatan</label><br>
    <textarea id="notes" name="notes">{{ old('notes', $distributionSchedule->notes ?? '') }}</textarea>
    @error('notes') <p style="color: #dc2626;">{{ $message }}</p> @enderror
</div>

<button type="submit">{{ $submitLabel }}</button>
