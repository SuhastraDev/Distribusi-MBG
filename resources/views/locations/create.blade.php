<x-layouts.app title="Tambah Lokasi Baru" breadcrumb="Data Master / Lokasi & Depot / Tambah">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Registrasi Lokasi atau Depot Baru</h2>
                <p class="text-sm text-slate-500">Tentukan koordinat GPS titik depot dapur masakan atau sekolah tujuan</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('locations.index') }}">
                &larr; Kembali ke Daftar
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('locations.store') }}">
                @include('locations._form', ['submitLabel' => 'Simpan Data Lokasi'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
