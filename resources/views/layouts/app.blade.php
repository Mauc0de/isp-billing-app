<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="min-h-screen bg-slate-50 font-sans text-slate-800 antialiased">
    {{-- Header mobile --}}
    <header class="sticky top-0 z-40 flex items-center justify-between border-b border-slate-200 bg-white px-4 py-3 md:hidden">
        <div class="flex items-center gap-3">
            <img src="{{ asset('satak.jpeg') }}" alt="Logo" class="h-8 w-8 rounded-lg border border-slate-100 object-contain">
            <span class="bg-gradient-to-r from-accent-600 via-brand-500 to-brand-800 bg-clip-text font-black tracking-tight text-transparent">SATAK</span>
        </div>
        <button id="btn-open" class="grid h-10 w-10 place-items-center rounded-xl border border-slate-200 active:bg-slate-100" aria-label="Buka menu">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
        </button>
    </header>

    <div id="backdrop" class="fixed inset-0 z-40 hidden bg-slate-900/40 backdrop-blur-sm md:hidden"></div>

    {{-- Drawer mobile --}}
    <aside id="drawer" class="fixed inset-y-0 left-0 z-50 flex w-[84%] max-w-[300px] -translate-x-full flex-col bg-white transition-transform duration-300 md:hidden">
        <div class="flex items-center justify-between border-b border-slate-100 p-5">
            <div class="flex items-center gap-3">
                <img src="{{ asset('satak.jpeg') }}" alt="Logo" class="h-9 w-9 rounded-xl border border-slate-100 object-contain">
                <div>
                    <h1 class="bg-gradient-to-r from-accent-600 via-brand-500 to-brand-800 bg-clip-text font-black leading-none text-transparent">SATAK</h1>
                    <p class="text-[10px] font-semibold tracking-wider text-slate-400">KONEK TERUS</p>
                </div>
            </div>
            <button id="btn-close" class="grid h-9 w-9 place-items-center rounded-xl bg-slate-100" aria-label="Tutup menu">
                <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M6 6l12 12M6 18L18 6"/></svg>
            </button>
        </div>
        <x-sidebar-nav />
        <div class="border-t border-slate-100 p-4">
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 hover:bg-rose-50"><span>🚪</span> Keluar</button>
            </form>
        </div>
    </aside>

    <div class="flex min-h-screen">
        {{-- Sidebar desktop --}}
        <aside class="fixed inset-y-0 left-0 z-20 hidden w-64 flex-col justify-between border-r border-slate-200 bg-white md:flex">
            <div>
                <div class="flex items-center gap-3 border-b border-slate-100 p-6">
                    <img src="{{ asset('satak.jpeg') }}" alt="Logo SATAK" class="h-10 w-10 rounded-xl border border-slate-100 object-contain shadow-sm">
                    <div>
                        <h1 class="bg-gradient-to-r from-accent-600 via-brand-500 to-brand-800 bg-clip-text text-lg font-black leading-tight tracking-tight text-transparent">SATAK</h1>
                        <p class="text-[11px] font-semibold tracking-wider text-slate-400">KONEK TERUS</p>
                    </div>
                </div>
                <x-sidebar-nav />
            </div>
            <div class="border-t border-slate-100 p-4">
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="flex w-full items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium text-rose-600 transition-colors hover:bg-rose-50"><span>🚪</span> Keluar</button>
                </form>
            </div>
        </aside>

        <main class="min-w-0 flex-1 md:ml-64">
            @yield('content')
        </main>
    </div>

    <script>
        const drawer = document.getElementById('drawer');
        const backdrop = document.getElementById('backdrop');
        const openBtn = document.getElementById('btn-open');
        const closeBtn = document.getElementById('btn-close');
        const openDrawer = () => { drawer.classList.remove('-translate-x-full'); backdrop.classList.remove('hidden'); document.body.style.overflow = 'hidden'; };
        const closeDrawer = () => { drawer.classList.add('-translate-x-full'); backdrop.classList.add('hidden'); document.body.style.overflow = ''; };
        openBtn?.addEventListener('click', openDrawer);
        closeBtn?.addEventListener('click', closeDrawer);
        backdrop?.addEventListener('click', closeDrawer);
    </script>
</body>
</html>
