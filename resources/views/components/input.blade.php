@props([
    'label' => null,
    'error' => null,
    'helper' => null,
    'type' => 'text',
    'id' => null,
])

@php
    $id = $id ?? $attributes->get('name', uniqid('input_'));
    $hasError = $error || ($attributes->get('name') && $errors->has($attributes->get('name')));
    $errorMessage = $error ?? ($attributes->get('name') ? $errors->first($attributes->get('name')) : null);
@endphp

<div class="w-full">
    @if ($label)
        <label for="{{ $id }}" class="block text-sm font-medium text-slate-700 mb-1">
            {{ $label }}
            @if ($attributes->has('required'))
                <span class="text-red-500">*</span>
            @endif
        </label>
    @endif

    <input
        type="{{ $type }}"
        id="{{ $id }}"
        {{ $attributes->merge([
            'class' => 'w-full px-3.5 py-2 text-sm rounded-lg border transition-colors bg-white text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-offset-1 ' .
                ($hasError 
                    ? 'border-red-300 focus:border-red-500 focus:ring-red-200 bg-red-50/20' 
                    : 'border-slate-300 hover:border-slate-400 focus:border-emerald-600 focus:ring-emerald-200')
        ]) }}
    />

    @if ($errorMessage)
        <p class="mt-1 text-xs text-red-600 flex items-center gap-1">
            <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
            {{ $errorMessage }}
        </p>
    @elseif ($helper)
        <p class="mt-1 text-xs text-slate-500">{{ $helper }}</p>
    @endif
</div>
