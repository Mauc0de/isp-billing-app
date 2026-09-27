<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <div class="flex min-h-screen">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-white border-r border-slate-200 fixed h-screen flex flex-col justify-between z-20">
            <div>
                <!-- Brand Header -->
                <div class="p-6 border-b border-slate-100 flex items-center gap-3">
                    <img src="{{ asset('satak.jpeg') }}" alt="Logo SATAK" class="w-10 h-10 rounded-xl object-contain shadow-sm border border-slate-100">
                    <div>
                        <h1 class="text-lg font-black tracking-tight bg-gradient-to-r from-[#00A896] via-[#0066FF] to-[#004BD6] bg-clip-text text-transparent leading-tight">SATAK</h1>
                        <p class="text-[11px] font-semibold text-slate-400 tracking-wider">KONEK TERUS</p>
                    </div>
                </div>

                <!-- Navigation -->
                <nav class="p-4 space-y-1.5 text-sm font-medium">
                    <a href="/dashboard" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('dashboard*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">📊</span> Dashboard
                    </a>
                    <a href="/pelanggan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pelanggan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">👥</span> Pelanggan
                    </a>
                    <a href="/pembayaran" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pembayaran*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">💳</span> Pembayaran
                    </a>
                    <a href="/tagihan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('tagihan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">🧾</span> Tagihan
                    </a>
                    <a href="/paket" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('paket*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">📦</span> Paket Internet
                    </a>
                    <a href="/laporan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('laporan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">📈</span> Laporan
                    </a>
                    <a href="/pengaturan" class="flex items-center gap-3 px-4 py-3 rounded-xl transition-all duration-200 {{ request()->is('pengaturan*') ? 'bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white shadow-md shadow-blue-500/20 font-semibold' : 'text-slate-600 hover:bg-slate-50 hover:text-[#0066FF]' }}">
                        <span class="text-base">⚙️</span> Pengaturan
                    </a>
                </nav>
            </div>

            <!-- Footer / Logout -->
            <div class="p-4 border-t border-slate-100">
                <a href="/logout" class="flex items-center gap-3 px-4 py-3 rounded-xl text-rose-600 hover:bg-rose-50 transition-colors text-sm font-medium">
                    <span class="text-base">🚪</span> Keluar
                </a>
            </div>
        </aside>

        <!-- MAIN VIEW -->
        <main class="ml-64 flex-1">
            @yield('content')
        </main>
    </div>
</body>
</html>
