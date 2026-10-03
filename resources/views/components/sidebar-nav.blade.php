@php
    // Grup menu supaya sidebar lebih mudah dipindai mata.
    $groups = [
        'Operasional' => [
            ['route' => 'dashboard', 'pattern' => 'dashboard*', 'label' => 'Dashboard', 'icon' => 'M3 12l9-9 9 9M5 10v10h14V10'],
            ['route' => 'pelanggan.index', 'pattern' => 'pelanggan*', 'label' => 'Pelanggan', 'icon' => 'M17 20h5v-2a4 4 0 00-3-3.87M9 20H4v-2a4 4 0 013-3.87M12 12a4 4 0 100-8 4 4 0 000 8z'],
            ['route' => 'paket.index', 'pattern' => 'paket*', 'label' => 'Paket Internet', 'icon' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4'],
        ],
        'Keuangan' => [
            ['route' => 'tagihan.index', 'pattern' => 'tagihan*', 'label' => 'Tagihan', 'icon' => 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z'],
            ['route' => 'pembayaran.index', 'pattern' => 'pembayaran*', 'label' => 'Pembayaran', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
            ['route' => 'laporan.index', 'pattern' => 'laporan*', 'label' => 'Laporan', 'icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z'],
        ],
    ];

    $canVouchers = auth()->user()?->can('vouchers.view') ?? false;
    $canSaldo = auth()->user()?->can('saldo.view') ?? false;
    $canPayments = auth()->user()?->can('payment_requests.view') ?? false;
    $canUsers = auth()->user()?->can('users.view') ?? false;
    $canRouters = auth()->user()?->can('routers.view') ?? false;
    $canExpenses = auth()->user()?->can('expenses.view') ?? false;

    if ($canVouchers || $canSaldo || $canPayments || $canUsers || $canRouters || $canExpenses) {
        $groups['Layanan Tambahan'] = array_values(array_filter([
            $canRouters ? ['route' => 'router.index', 'pattern' => 'router*', 'label' => 'Router', 'icon' => 'M5 12h14M12 5l7 7-7 7'] : null,
            $canVouchers ? ['route' => 'vouchers.index', 'pattern' => 'voucher*', 'label' => 'Voucher', 'icon' => 'M15 5v2m0 4v2m0 4v2M5 5h14a2 2 0 012 2v3a2 2 0 000 4v3a2 2 0 01-2 2H5a2 2 0 01-2-2v-3a2 2 0 000-4V7a2 2 0 012-2z'] : null,
            $canSaldo ? ['route' => 'saldo.index', 'pattern' => 'saldo*', 'label' => 'Saldo', 'icon' => 'M12 8c-2.21 0-4 .895-4 2s1.79 2 4 2 4 .895 4 2-1.79 2-4 2m0-8c1.48 0 2.773.402 3.5 1M12 8V6m0 10c-1.48 0-2.773-.402-3.5-1M12 16v2m9-5a9 9 0 11-18 0 9 9 0 0118 0z'] : null,
            $canPayments ? ['route' => 'payment-requests.index', 'pattern' => 'pembayaran-masuk*', 'label' => 'Pembayaran Masuk', 'icon' => 'M20 13V7a2 2 0 00-2-2H6a2 2 0 00-2 2v6m16 0l-8 5-8-5m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5'] : null,
            $canExpenses ? ['route' => 'pengeluaran.index', 'pattern' => 'pengeluaran*', 'label' => 'Pengeluaran', 'icon' => 'M3 17l6-6 4 4 8-8M21 7h-6m6 0v6'] : null,
            $canUsers ? ['route' => 'users.index', 'pattern' => 'pengguna*', 'label' => 'Pengguna', 'icon' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z'] : null,
        ], fn ($item) => $item !== null));
    }

    $groups['Sistem'] = [
        ['route' => 'pengaturan.index', 'pattern' => 'pengaturan*', 'label' => 'Pengaturan', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z'],
    ];
@endphp

<div {{ $attributes->merge(['class' => 'flex-1 space-y-5 overflow-y-auto px-4 py-5']) }}>
    @foreach($groups as $group => $items)
        <div>
            <p class="px-3 text-[10px] font-bold uppercase tracking-widest text-slate-400">{{ $group }}</p>
            <nav class="mt-2 space-y-1 text-sm font-medium">
                @foreach($items as $item)
                    @php($active = request()->is($item['pattern']))
                    <a href="{{ route($item['route']) }}"
                       class="flex items-center gap-3 rounded-xl px-3 py-2.5 transition-all duration-200 {{ $active ? 'bg-gradient-to-r from-accent-500 via-brand-500 to-brand-600 text-white shadow-md shadow-brand-500/20 font-semibold' : 'text-slate-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.9" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}" />
                        </svg>
                        {{ $item['label'] }}
                    </a>
                @endforeach
            </nav>
        </div>
    @endforeach
</div>
