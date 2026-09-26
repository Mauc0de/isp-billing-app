<!DOCTYPE html>
<html lang="id">
<<<<<<< HEAD
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>{{ config('app.name', 'ISP Billing') }}</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles
    </head>
    <body>
        {{ $slot }}
        @livewireScripts
    </body>
=======
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - @yield('title')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- SIDEBAR -->
        <aside class="w-64 bg-blue-950 text-white fixed h-screen">
            <div class="p-6 border-b border-blue-800">
                <h1 class="text-2xl font-bold">SATAK</h1>
                <p class="text-blue-300 text-sm">Sistem Keuangan ISP</p>
            </div>
            <nav class="p-4 space-y-2">
                <a href="/dashboard" class="block px-4 py-3 rounded-lg bg-blue-800">📊 Dashboard</a>
                <a href="/pelanggan" class="block px-4 py-3 rounded-lg hover:bg-blue-800">👥 Pelanggan</a>
                <a href="/pembayaran" class="block px-4 py-3 rounded-lg hover:bg-blue-800">💳 Pembayaran</a>
                <a href="/tagihan" class="block px-4 py-3 rounded-lg hover:bg-blue-800">🧾 Tagihan</a>
                <a href="/paket" class="block px-4 py-3 rounded-lg hover:bg-blue-800">📦 Paket Internet</a>
                <a href="/laporan" class="block px-4 py-3 rounded-lg hover:bg-blue-800">📈 Laporan</a>
                <a href="/pengaturan" class="block px-4 py-3 rounded-lg hover:bg-blue-800">⚙️ Pengaturan</a>
            </nav>
        </aside>
        <main class="ml-64 flex-1">
            @yield('content')
        </main>
    </div>
</body>
>>>>>>> origin/main
</html>