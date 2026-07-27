<x-layouts.app>
    <h1>Buat Distribusi Aktual</h1>
    <p>Pilih jadwal terjadwal yang belum pernah dibuatkan distribusi aktual.</p>

    <form method="POST" action="{{ route('distribution-runs.store') }}">
        @csrf

        <div>
            <label for="distribution_schedule_id">Jadwal Distribusi</label><br>
            <select id="distribution_schedule_id" name="distribution_schedule_id" required>
                <option value="">Pilih jadwal</option>
                @foreach ($schedules as $schedule)
                    <option value="{{ $schedule->id }}" @selected((int) old('distribution_schedule_id') === $schedule->id)>
                        {{ $schedule->code }} - {{ $schedule->scheduled_date->format('d/m/Y') }} - {{ $schedule->officer->name }} - {{ $schedule->depot->name }}
                    </option>
                @endforeach
            </select>
            @error('distribution_schedule_id') <p style="color: #dc2626;">{{ $message }}</p> @enderror
        </div>

        <div>
            <label for="notes">Catatan Awal</label><br>
            <textarea id="notes" name="notes">{{ old('notes') }}</textarea>
            @error('notes') <p style="color: #dc2626;">{{ $message }}</p> @enderror
        </div>

        <button type="submit">Buat Distribusi</button>
    </form>

    <p><a href="{{ route('distribution-runs.index') }}">Kembali ke distribusi aktual</a></p>
</x-layouts.app>
