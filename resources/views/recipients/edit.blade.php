<x-layouts.app>
    <h1>Edit Penerima MBG</h1>

    <form method="POST" action="{{ route('recipients.update', $recipient) }}">
        @method('PUT')
        @include('recipients._form', ['submitLabel' => 'Update Penerima'])
    </form>
</x-layouts.app>
