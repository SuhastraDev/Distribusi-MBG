@props([
    'variant' => 'default',
])

@php
    $variants = [
        'default' => 'bg-slate-100 text-slate-700 border-slate-200',
        'draft' => 'bg-slate-100 text-slate-700 border-slate-200',
        
        'success' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'selesai' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'delivered' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',

        'warning' => 'bg-amber-50 text-amber-700 border-amber-200',
        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
        'berjalan' => 'bg-amber-50 text-amber-700 border-amber-200',
        'in_progress' => 'bg-amber-50 text-amber-700 border-amber-200',
        'processing' => 'bg-amber-50 text-amber-700 border-amber-200',

        'danger' => 'bg-red-50 text-red-700 border-red-200',
        'inactive' => 'bg-red-50 text-red-700 border-red-200',
        'terlambat' => 'bg-red-50 text-red-700 border-red-200',
        'cancelled' => 'bg-red-50 text-red-700 border-red-200',
        'failed' => 'bg-red-50 text-red-700 border-red-200',

        'info' => 'bg-blue-50 text-blue-700 border-blue-200',
        'ready' => 'bg-blue-50 text-blue-700 border-blue-200',
        'generated' => 'bg-blue-50 text-blue-700 border-blue-200',
        'scheduled' => 'bg-blue-50 text-blue-700 border-blue-200',
        'depot' => 'bg-blue-50 text-blue-700 border-blue-200',
        'sekolah' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'puskesmas' => 'bg-rose-50 text-rose-700 border-rose-200',
    ];

    $classes = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border ' . ($variants[strtolower($variant)] ?? $variants['default']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
