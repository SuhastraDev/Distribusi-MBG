<x-layouts.app>
    <h1>Edit Jadwal Distribusi</h1>

    <form method="POST" action="{{ route('distribution-schedules.update', $distributionSchedule) }}">
        @method('PUT')
        @include('distribution-schedules._form', ['submitLabel' => 'Perbarui Jadwal'])
    </form>

    <p><a href="{{ route('distribution-schedules.show', $distributionSchedule) }}">Kembali ke detail jadwal</a></p>
</x-layouts.app>
