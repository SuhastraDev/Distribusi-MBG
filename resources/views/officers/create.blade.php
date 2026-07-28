<x-layouts.app title="Tambah Petugas Baru" breadcrumb="Data Master / Petugas / Tambah">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Registrasi Petugas Distribusi Baru</h2>
                <p class="text-sm text-slate-500">Daftarkan personel baru beserta akun login untuk aplikasi</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('officers.index') }}">
                &larr; Kembali ke Daftar
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('officers.store') }}">
                @include('officers._form', ['submitLabel' => 'Simpan Data Petugas'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
