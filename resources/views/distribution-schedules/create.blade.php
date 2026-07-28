<x-layouts.app title="Buat Jadwal Distribusi Baru" breadcrumb="Operasional / Jadwal Distribusi / Tambah">
    <div class="max-w-5xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Penyusunan Rencana Jadwal Distribusi MBG</h2>
                <p class="text-sm text-slate-500">Tentukan tanggal, petugas lapangan, depot asal, serta daftar sekolah penerima</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('distribution-schedules.index') }}">
                &larr; Kembali ke Daftar
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('distribution-schedules.store') }}">
                @include('distribution-schedules._form', ['submitLabel' => 'Simpan Jadwal Distribusi'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
