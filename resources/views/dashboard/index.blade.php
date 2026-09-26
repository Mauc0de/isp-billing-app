<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>SATAK Keuangan - Dashboard</title>

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="bg-gray-100">

    <div class="flex min-h-screen">

        <!-- SIDEBAR -->
        <aside class="w-64 bg-blue-950 text-white fixed h-screen">

            <!-- LOGO -->
            <div class="p-6 border-b border-blue-800">
                <h1 class="text-2xl font-bold">
                    SATAK
                </h1>

                <p class="text-blue-300 text-sm">
                    Sistem Keuangan ISP
                </p>
            </div>

            <!-- MENU -->
            <nav class="p-4 space-y-2">

                <a href="/dashboard"
                   class="block px-4 py-3 rounded-lg bg-blue-800">
                    📊 Dashboard
                </a>

                <a href="{{ route('customers.index') }}"
                   class="block px-4 py-3 rounded-lg hover:bg-blue-800">
                    👥 Pelanggan
                </a>

                <a href="#"
                   class="block px-4 py-3 rounded-lg hover:bg-blue-800">
                    💳 Pembayaran
                </a>

                <a href="#"
                   class="block px-4 py-3 rounded-lg hover:bg-blue-800">
                    🧾 Tagihan
                </a>

                <a href="#"
                   class="block px-4 py-3 rounded-lg hover:bg-blue-800">
                    📦 Paket Internet
                </a>

                <a href="#"
                   class="block px-4 py-3 rounded-lg hover:bg-blue-800">
                    📈 Laporan
                </a>

                <a href="#"
                   class="block px-4 py-3 rounded-lg hover:bg-blue-800">
                    ⚙️ Pengaturan
                </a>

            </nav>

        </aside>


        <!-- CONTENT -->
        <main class="ml-64 flex-1">

            <!-- NAVBAR -->
            <header class="bg-white shadow-sm px-8 py-5 flex justify-between items-center">

                <div>
                    <h2 class="text-2xl font-bold text-gray-800">
                        Dashboard
                    </h2>

                    <p class="text-gray-500 text-sm">
                        Ringkasan keuangan dan pelanggan ISP
                    </p>
                </div>

                <div class="flex items-center gap-4">

                    <div class="text-right">
                        <p class="font-semibold text-gray-800">
                            Admin
                        </p>

                        <p class="text-sm text-gray-500">
                            Administrator
                        </p>
                    </div>

                    <div class="w-10 h-10 rounded-full bg-blue-900 text-white flex items-center justify-center font-bold">
                        A
                    </div>

                </div>

            </header>


            <!-- DASHBOARD CONTENT -->
            <div class="p-8">

                <!-- WELCOME -->
                <div class="bg-blue-900 rounded-2xl p-7 text-white mb-8">

                    <h2 class="text-2xl font-bold mb-2">
                        Selamat Datang di SATAK 👋
                    </h2>

                    <p class="text-blue-200">
                        Kelola pelanggan, pembayaran, tagihan dan keuangan ISP
                        melalui satu dashboard.
                    </p>

                </div>


                <!-- STATISTIK -->
                <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 mb-8">

                    <!-- PELANGGAN -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">

                        <div class="flex justify-between items-start">

                            <div>
                                <p class="text-gray-500 text-sm">
                                    Total Pelanggan
                                </p>

                                <h3 class="text-3xl font-bold text-gray-800 mt-2">
                                    125
                                </h3>

                                <p class="text-green-600 text-sm mt-2">
                                    ↑ 8% bulan ini
                                </p>
                            </div>

                            <div class="bg-blue-100 p-3 rounded-xl text-2xl">
                                👥
                            </div>

                        </div>

                    </div>


                    <!-- PENDAPATAN -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">

                        <div class="flex justify-between items-start">

                            <div>
                                <p class="text-gray-500 text-sm">
                                    Pendapatan Bulan Ini
                                </p>

                                <h3 class="text-2xl font-bold text-gray-800 mt-2">
                                    Rp 15.750.000
                                </h3>

                                <p class="text-green-600 text-sm mt-2">
                                    ↑ 12% dari bulan lalu
                                </p>
                            </div>

                            <div class="bg-green-100 p-3 rounded-xl text-2xl">
                                💰
                            </div>

                        </div>

                    </div>


                    <!-- TAGIHAN -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">

                        <div class="flex justify-between items-start">

                            <div>
                                <p class="text-gray-500 text-sm">
                                    Tagihan Belum Dibayar
                                </p>

                                <h3 class="text-3xl font-bold text-gray-800 mt-2">
                                    18
                                </h3>

                                <p class="text-red-600 text-sm mt-2">
                                    Perlu ditindaklanjuti
                                </p>
                            </div>

                            <div class="bg-red-100 p-3 rounded-xl text-2xl">
                                🧾
                            </div>

                        </div>

                    </div>


                    <!-- JATUH TEMPO -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">

                        <div class="flex justify-between items-start">

                            <div>
                                <p class="text-gray-500 text-sm">
                                    Jatuh Tempo Hari Ini
                                </p>

                                <h3 class="text-3xl font-bold text-gray-800 mt-2">
                                    7
                                </h3>

                                <p class="text-orange-600 text-sm mt-2">
                                    Perlu perhatian
                                </p>
                            </div>

                            <div class="bg-orange-100 p-3 rounded-xl text-2xl">
                                ⏰
                            </div>

                        </div>

                    </div>

                </div>


                <!-- BAGIAN BAWAH -->
                <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

                    <!-- TRANSAKSI -->
                    <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm">

                        <div class="p-6 border-b flex justify-between items-center">

                            <div>
                                <h3 class="font-bold text-lg">
                                    Transaksi Terbaru
                                </h3>

                                <p class="text-gray-500 text-sm">
                                    Pembayaran pelanggan terbaru
                                </p>
                            </div>

                            <a href="#" class="text-blue-700 text-sm font-semibold">
                                Lihat Semua
                            </a>

                        </div>


                        <div class="overflow-x-auto">

                            <table class="w-full">

                                <thead class="bg-gray-50">

                                    <tr>
                                        <th class="text-left p-4 text-sm text-gray-500">
                                            Pelanggan
                                        </th>

                                        <th class="text-left p-4 text-sm text-gray-500">
                                            Paket
                                        </th>

                                        <th class="text-left p-4 text-sm text-gray-500">
                                            Jumlah
                                        </th>

                                        <th class="text-left p-4 text-sm text-gray-500">
                                            Status
                                        </th>
                                    </tr>

                                </thead>

                                <tbody>

                                    <tr class="border-t">

                                        <td class="p-4 font-medium">
                                            Ahmad
                                        </td>

                                        <td class="p-4 text-gray-500">
                                            10 Mbps
                                        </td>

                                        <td class="p-4">
                                            Rp 150.000
                                        </td>

                                        <td class="p-4">
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                                Lunas
                                            </span>
                                        </td>

                                    </tr>


                                    <tr class="border-t">

                                        <td class="p-4 font-medium">
                                            Budi
                                        </td>

                                        <td class="p-4 text-gray-500">
                                            20 Mbps
                                        </td>

                                        <td class="p-4">
                                            Rp 200.000
                                        </td>

                                        <td class="p-4">
                                            <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs">
                                                Lunas
                                            </span>
                                        </td>

                                    </tr>


                                    <tr class="border-t">

                                        <td class="p-4 font-medium">
                                            Candra
                                        </td>

                                        <td class="p-4 text-gray-500">
                                            10 Mbps
                                        </td>

                                        <td class="p-4">
                                            Rp 150.000
                                        </td>

                                        <td class="p-4">
                                            <span class="bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs">
                                                Belum Bayar
                                            </span>
                                        </td>

                                    </tr>

                                </tbody>

                            </table>

                        </div>

                    </div>


                    <!-- STATUS -->
                    <div class="bg-white rounded-2xl shadow-sm p-6">

                        <h3 class="font-bold text-lg">
                            Status Pelanggan
                        </h3>

                        <p class="text-gray-500 text-sm mb-6">
                            Kondisi pelanggan saat ini
                        </p>


                        <div class="space-y-5">

                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm">
                                        Aktif
                                    </span>

                                    <span class="font-semibold">
                                        105
                                    </span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-green-500 h-2 rounded-full w-[84%]"></div>
                                </div>
                            </div>


                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm">
                                        Menunggak
                                    </span>

                                    <span class="font-semibold">
                                        12
                                    </span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-red-500 h-2 rounded-full w-[10%]"></div>
                                </div>
                            </div>


                            <div>
                                <div class="flex justify-between mb-2">
                                    <span class="text-sm">
                                        Nonaktif
                                    </span>

                                    <span class="font-semibold">
                                        8
                                    </span>
                                </div>

                                <div class="w-full bg-gray-200 rounded-full h-2">
                                    <div class="bg-gray-500 h-2 rounded-full w-[6%]"></div>
                                </div>
                            </div>

                        </div>

                    </div>

                </div>

            </div>

        </main>

    </div>

</body>
</html>
