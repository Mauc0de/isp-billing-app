@props([
    'title' => '',
    'subtitle' => null,
])

<header {{ $attributes->merge(['class' => 'bg-white/80 backdrop-blur border-b border-slate-200 sticky top-0 z-10']) }}>
    <div class="px-4 md:px-8 py-4 md:py-5 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
        <div>
            <h2 class="text-xl md:text-2xl font-bold text-slate-900 tracking-tight">{{ $title }}</h2>
            @if($subtitle)
                <p class="text-slate-500 text-xs md:text-sm mt-0.5">{{ $subtitle }}</p>
            @endif
        </div>
        @isset($actions)
            <div class="flex items-center gap-3">{{ $actions }}</div>
        @endisset
        {{ $slot }}
    </div>
</header>
