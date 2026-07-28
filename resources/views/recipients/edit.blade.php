<x-layouts.app title="Edit Penerima: {{ $recipient->name }}" breadcrumb="Data Master / Penerima MBG / Edit">
    <div class="max-w-4xl mx-auto space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h2 class="text-xl font-bold text-slate-800">Edit Penerima: {{ $recipient->name }}</h2>
                <p class="text-sm text-slate-500">Perbarui alokasi porsi atau perubahan lokasi sekolah</p>
            </div>
            <x-button variant="outline" size="sm" href="{{ route('recipients.show', $recipient) }}">
                &larr; Lihat Detail
            </x-button>
        </div>

        <x-card padding="p-6 sm:p-8">
            <form method="POST" action="{{ route('recipients.update', $recipient) }}">
                @method('PUT')
                @include('recipients._form', ['submitLabel' => 'Update Data Penerima'])
            </form>
        </x-card>
    </div>
</x-layouts.app>
