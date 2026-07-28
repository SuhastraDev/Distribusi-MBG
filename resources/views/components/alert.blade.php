@props([
    'variant' => 'info',
    'title' => null,
    'dismissible' => true,
])

@php
    $variants = [
        'success' => [
            'bg' => 'bg-emerald-50 border-emerald-200 text-emerald-800',
            'icon' => 'text-emerald-500',
        ],
        'error' => [
            'bg' => 'bg-red-50 border-red-200 text-red-800',
            'icon' => 'text-red-500',
        ],
        'danger' => [
            'bg' => 'bg-red-50 border-red-200 text-red-800',
            'icon' => 'text-red-500',
        ],
        'warning' => [
            'bg' => 'bg-amber-50 border-amber-200 text-amber-800',
            'icon' => 'text-amber-500',
        ],
        'info' => [
            'bg' => 'bg-blue-50 border-blue-200 text-blue-800',
            'icon' => 'text-blue-500',
        ],
    ];

    $config = $variants[$variant] ?? $variants['info'];
@endphp

<div x-data="{ show: true }" x-show="show" x-transition
    {{ $attributes->merge(['class' => 'p-4 rounded-xl border flex items-start justify-between gap-3 shadow-2xs ' . $config['bg']]) }}
    role="alert">
    <div class="flex items-start gap-3 w-full">
        <div class="mt-0.5 shrink-0 {{ $config['icon'] }}">
            @if ($variant === 'success')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif ($variant === 'error' || $variant === 'danger')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @elseif ($variant === 'warning')
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
            @else
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            @endif
        </div>
        <div class="text-sm">
            @if ($title)
                <h4 class="font-semibold mb-1">{{ $title }}</h4>
            @endif
            <div class="leading-relaxed">
                {{ $slot }}
            </div>
        </div>
    </div>

    @if ($dismissible)
        <button @click="show = false" type="button" class="shrink-0 p-1 rounded-lg opacity-70 hover:opacity-100 transition-opacity focus:outline-none">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
        </button>
    @endif
</div>
