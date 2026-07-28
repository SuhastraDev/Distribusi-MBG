<x-layouts.app title="Edit Petugas - {{ $officer->name }}" breadcrumb="Data Master / Petugas / Edit">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Data Petugas: {{ $officer->name }}</h2>
                <p class="text-sm text-slate-500">Perbarui profil personel lapangan atau kredensial akun login</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('officers.show', $officer) }}">
                &larr; Lihat Detail
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('officers.update', $officer) }}">
                @method('PUT')
                @include('officers._form', ['submitLabel' => 'Update Data Petugas'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
