<x-layouts.app title="Edit Lokasi: {{ $location->name }}" breadcrumb="Data Master / Lokasi & Depot / Edit">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Lokasi: {{ $location->name }}</h2>
                <p class="text-sm text-slate-500">Perbarui koordinat geografis atau status operasional titik ini</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('locations.show', $location) }}">
                &larr; Lihat Detail
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('locations.update', $location) }}">
                @method('PUT')
                @include('locations._form', ['submitLabel' => 'Update Data Lokasi'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
