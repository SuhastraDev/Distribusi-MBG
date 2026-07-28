@props([
    'title' => null,
    'subtitle' => null,
    'headerAction' => null,
    'footer' => null,
    'padding' => 'p-6',
])

<div {{ $attributes->merge(['class' => 'bg-white rounded-xl border border-slate-200/80 shadow-sm overflow-hidden transition-all duration-200 hover:shadow-md']) }}>
    @if ($title || $headerAction || isset($header))
        <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between gap-4 bg-slate-50/50">
            <div>
                @if ($title)
                    <h3 class="font-semibold text-slate-800 text-base">{{ $title }}</h3>
                @endif
                @if ($subtitle)
                    <p class="text-xs text-slate-500 mt-0.5">{{ $subtitle }}</p>
                @endif
                {{ $header ?? '' }}
            </div>
            @if ($headerAction)
                <div>
                    {{ $headerAction }}
                </div>
            @endif
        </div>
    @endif

    <div class="{{ $padding }}">
        {{ $slot }}
    </div>

    @if ($footer)
        <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 text-sm text-slate-600">
            {{ $footer }}
        </div>
    @endif
</div>
