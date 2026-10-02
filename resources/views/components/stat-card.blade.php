@props([
    'label' => null,
    'value' => null,
    'hint' => null,
    'tone' => 'slate',
])

@php
    $valueTones = [
        'slate' => 'text-slate-900',
        'emerald' => 'text-emerald-600',
        'amber' => 'text-amber-600',
        'rose' => 'text-rose-600',
        'brand' => 'text-brand-600',
    ];
    $valueClass = $valueTones[$tone] ?? $valueTones['slate'];
@endphp

<div {{ $attributes->merge(['class' => 'rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm']) }}>
    @if($label)
        <p class="text-xs font-medium text-slate-400">{{ $label }}</p>
    @endif
    <p class="mt-1.5 text-2xl font-extrabold tracking-tight {{ $valueClass }}">
        {{ $value ?? $slot }}
    </p>
    @if($hint)
        <p class="mt-2 text-[11px] font-semibold text-slate-400">{{ $hint }}</p>
    @endif
</div>
