<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <header class="md:hidden sticky top-0 z-40 bg-white border-b border-slate-200 flex items-center justify-between px-4 py-3">
        <div class="flex items-center gap-3">
            <img src="{{ asset('satak.jpeg') }}" alt="Logo" class="w-8 h-8 rounded-lg object-contain border border-slate-100">
            <span class="font-black tracking-tight bg-gradient-to-r from-[#00A896] via-[#0066FF] to-[#004BD6] bg-clip-text text-transparent">SATAK</span>
        </div>
        <button id="btn-open" class="w-10 h-10 grid place-items-center rounded-xl border border-slate-200 active:bg-slate-100" aria-label="Menu">☰</button>
    </header>

    <div id="backdrop" class="hidden fixed inset-0 bg-slate-900/40 backdrop-blur-sm z-40 md:hidden"></div>
    <aside id="drawer" class="fixed inset-y-0 left-0 w-[84%] max-w-[300px] bg-white z-50 -translate-x-full transition-transform duration-300 md:hidden flex flex-col">
        <div class="p-5 border-b border-slate-100 flex items-center justify-between">
            <div class="flex items-center gap-3">
                <img src="{{ asset('satak.jpeg') }}" alt="Logo" class="w-9 h-9 rounded-xl object-contain border border-slate-100">
                <div>
                    <h1 class="font-black leading-none bg-gradient-to-r from-[#00A896] via-[#0066FF] to-[#004BD6] bg-clip-text text-transparent">SATAK</h1>
                    <p class="text-[10px] font-semibold text-slate-400 tracking-wider">KONEK TERUS</p>
                </div>
            </div>
            <button id="btn-close" class="w-9 h-9 grid place-items-center rounded-xl bg-slate-100">✕</button>
        </div>
        <nav class="p-4 space-y-1.5 text-sm font-medium flex-1 overflow-y-auto">
            <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('dashboard*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>📊</span> Dashboard</a>
            <a href="/pelanggan" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('pelanggan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>👥</span> Pelanggan</a>
            <a href="/pembayaran" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('pembayaran*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>💳</span> Pembayaran</a>
            <a href="/tagihan" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('tagihan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>🧾</span> Tagihan</a>
            <a href="/paket" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('paket*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>📦</span> Paket Internet</a>
            <a href="/laporan" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('laporan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>📈</span> Laporan</a>
            <a href="/pengaturan" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('pengaturan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>⚙️</span> Pengaturan</a>
            @can('users.view')
            <a href="/pengguna" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('pengguna*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>🧑‍💼</span> Pengguna</a>
            @endcan
            @can('vouchers.view')
            <a href="/voucher" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('voucher*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>🎟️</span> Voucher</a>
            @endcan
            @can('saldo.view')
            <a href="/saldo" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('saldo*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>💰</span> Saldo</a>
            @endcan
            @can('payment_requests.view')
            <a href="/pembayaran-masuk" class="flex items-center gap-3 px-4 py-3 rounded-xl {{ request()->is('pembayaran-masuk*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md font-semibold' : 'text-slate-600 hover:bg-slate-50' }}"><span>📥</span> Pembayaran Masuk</a>
            @endcan
        </nav>
        <div class="p-4 border-t border-slate-100">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 text-sm font-medium"><span>🚪</span> Keluar</button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen">
        <aside class="hidden md:flex w-64 bg-white border-r border-slate-200 fixed inset-y-0 left-0 flex-col justify-between z-20">
            <div>
                <div class="p-6 border-b border-slate-100 flex items-center gap-3">
                    <img src="{{ asset('satak.jpeg') }}" alt="Logo SATAK" class="w-10 h-10 rounded-xl object-contain shadow-sm border border-slate-100">
                    <div>
                        <h1 class="text-lg font-black tracking-tight bg-gradient-to-r from-[#00A896] via-[#0066FF] to-[#004BD6] bg-clip-text text-transparent leading-tight">SATAK</h1>
                        <p class="text-[11px] font-semibold text-slate-400 tracking-wider">KONEK TERUS</p>
                    </div>
                </div>
                <nav class="p-4 space-y-1.5 text-sm font-medium">
                    <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('dashboard*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">📊</span> Dashboard</a>
                    <a href="/pelanggan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pelanggan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">👥</span> Pelanggan</a>
                    <a href="/pembayaran" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pembayaran*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">💳</span> Pembayaran</a>
                    <a href="/tagihan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('tagihan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">🧾</span> Tagihan</a>
                    <a href="/paket" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('paket*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">📦</span> Paket Internet</a>
                    <a href="/laporan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('laporan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">📈</span> Laporan</a>
                    <a href="/pengaturan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pengaturan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">⚙️</span> Pengaturan</a>
                    @can('users.view')
                    <a href="/pengguna" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pengguna*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">🧑‍💼</span> Pengguna</a>
                    @endcan
                    @can('vouchers.view')
                    <a href="/voucher" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('voucher*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">🎟️</span> Voucher</a>
                    @endcan
                    @can('saldo.view')
                    <a href="/saldo" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('saldo*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">💰</span> Saldo</a>
                    @endcan
                    @can('payment_requests.view')
                    <a href="/pembayaran-masuk" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pembayaran-masuk*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}"><span class="text-base">📥</span> Pembayaran Masuk</a>
                    @endcan
                </nav>
            </div>
            <div class="p-4 border-t border-slate-100">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 transition-colors text-sm font-medium"><span class="text-base">🚪</span> Keluar</button>
                </form>
            </div>
        </aside>

        <main class="flex-1 min-w-0 md:ml-64">
            @yield('content')
        </main>
    </div>

    <script>
        const drawer=document.getElementById('drawer');
        const backdrop=document.getElementById('backdrop');
        const openBtn=document.getElementById('btn-open');
        const closeBtn=document.getElementById('btn-close');
        function openDrawer(){drawer.classList.remove('-translate-x-full');backdrop.classList.remove('hidden');document.body.style.overflow='hidden';}
        function closeDrawer(){drawer.classList.add('-translate-x-full');backdrop.classList.add('hidden');document.body.style.overflow='';}
        openBtn?.addEventListener('click',openDrawer);
        closeBtn?.addEventListener('click',closeDrawer);
        backdrop?.addEventListener('click',closeDrawer);
    </script>
</body>
</html>
