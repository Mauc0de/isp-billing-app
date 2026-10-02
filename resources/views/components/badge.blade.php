@props([
    'tone' => 'slate',
])

@php
    $tones = [
        'emerald' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
        'amber' => 'bg-amber-50 text-amber-700 border-amber-200',
        'rose' => 'bg-rose-50 text-rose-600 border-rose-200',
        'brand' => 'bg-brand-50 text-brand-700 border-brand-200',
        'slate' => 'bg-slate-100 text-slate-600 border-slate-200',
    ];
    $classes = $tones[$tone] ?? $tones['slate'];
@endphp

<span {{ $attributes->merge(['class' => "inline-flex items-center gap-1.5 rounded-full border px-2.5 py-1 text-[11px] font-bold whitespace-nowrap {$classes}"]) }}>
    {{ $slot }}
</span>
