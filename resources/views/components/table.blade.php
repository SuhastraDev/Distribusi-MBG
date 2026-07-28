@props([
    'headers' => [],
])

<div class="overflow-x-auto w-full">
    <table {{ $attributes->merge(['class' => 'w-full text-left border-collapse text-sm text-slate-600']) }}>
        @if (count($headers) > 0 || isset($thead))
            <thead class="bg-slate-50/80 text-xs uppercase text-slate-500 font-semibold border-b border-slate-200">
                <tr>
                    @if (count($headers) > 0)
                        @foreach ($headers as $header)
                            <th scope="col" class="px-4 py-3 whitespace-nowrap">{{ $header }}</th>
                        @endforeach
                    @else
                        {{ $thead }}
                    @endif
                </tr>
            </thead>
        @endif
        <tbody class="divide-y divide-slate-100 bg-white">
            {{ $slot }}
        </tbody>
    </table>
</div>
