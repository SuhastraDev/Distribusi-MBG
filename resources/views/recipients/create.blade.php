<x-layouts.app title="Tambah Penerima Baru" breadcrumb="Data Master / Penerima MBG / Tambah">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Registrasi Penerima MBG Baru</h2>
                <p class="text-sm text-slate-500">Tentukan sekolah, lembaga, atau sasaran alokasi porsi harian</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('recipients.index') }}">
                &larr; Kembali ke Daftar
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('recipients.store') }}">
                @include('recipients._form', ['submitLabel' => 'Simpan Data Penerima'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
