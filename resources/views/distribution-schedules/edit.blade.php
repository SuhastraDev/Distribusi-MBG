<x-layouts.app title="Edit Jadwal: {{ $distributionSchedule->code }}" breadcrumb="Operasional / Jadwal Distribusi / Edit">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Rencana Jadwal: {{ $distributionSchedule->code }}</h2>
                <p class="text-sm text-slate-500">Perbarui tanggal, penugasan personel, atau sesuaikan kembali tujuan distribusi</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('distribution-schedules.show', $distributionSchedule) }}">
                &larr; Lihat Detail
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('distribution-schedules.update', $distributionSchedule) }}">
                @method('PUT')
                @include('distribution-schedules._form', ['submitLabel' => 'Update Jadwal Distribusi'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
