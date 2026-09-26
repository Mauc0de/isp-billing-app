<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - Sistem Keuangan ISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gray-50">
    <nav class="bg-white shadow-sm sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-blue-900 text-white flex items-center justify-center font-bold">S</div>
                <div>
                    <h1 class="font-bold text-blue-900 leading-none">SATAK</h1>
                    <p class="text-xs text-gray-500">Sistem Keuangan ISP</p>
                </div>
            </div>
            <a href="/login" class="bg-blue-900 text-white px-6 py-2.5 rounded-xl hover:bg-blue-800 transition font-medium text-sm">Masuk Dashboard →</a>
        </div>
    </nav>

    <section class="max-w-7xl mx-auto px-6 py-16 lg:py-24">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div>
                <span class="inline-block bg-blue-100 text-blue-800 px-4 py-1.5 rounded-full text-xs font-semibold mb-4">PT Nusa Data Koneksi Usaha</span>
                <h2 class="text-4xl lg:text-5xl font-bold text-gray-900 leading-tight">Kelola Keuangan ISP<br><span class="text-blue-900">Lebih Mudah</span></h2>
                <p class="text-gray-500 mt-4 text-lg leading-relaxed">SATAK bantu kelola pelanggan, tagihan, pembayaran, dan laporan keuangan dalam satu dashboard terintegrasi.</p>
                <div class="flex gap-4 mt-8">
                    <a href="/dashboard" class="bg-blue-900 text-white px-8 py-3.5 rounded-xl hover:bg-blue-800 transition font-semibold shadow-lg shadow-blue-900/20">Masuk Dashboard</a>
                    <a href="#fitur" class="bg-white border border-gray-200 px-8 py-3.5 rounded-xl hover:bg-gray-50 transition font-semibold">Lihat Fitur</a>
                </div>
                <div class="flex gap-8 mt-10">
                    <div><p class="text-2xl font-bold text-gray-900">125+</p><p class="text-sm text-gray-500">Pelanggan</p></div>
                    <div><p class="text-2xl font-bold text-gray-900">99%</p><p class="text-sm text-gray-500">Akurasi</p></div>
                    <div><p class="text-2xl font-bold text-gray-900">24/7</p><p class="text-sm text-gray-500">Monitoring</p></div>
                </div>
            </div>
            <div class="relative">
                <div class="bg-blue-900 rounded-3xl p-8 text-white shadow-2xl">
                    <p class="text-blue-200 text-sm">Ringkasan Hari Ini</p>
                    <div class="grid grid-cols-2 gap-4 mt-4">
                        <div class="bg-white/10 backdrop-blur rounded-2xl p-4"><p class="text-blue-200 text-xs">Pendapatan</p><p class="text-xl font-bold mt-1">Rp 15,7 jt</p><p class="text-green-300 text-xs mt-1">↑ 12%</p></div>
                        <div class="bg-white/10 backdrop-blur rounded-2xl p-4"><p class="text-blue-200 text-xs">Tagihan Aktif</p><p class="text-xl font-bold mt-1">18</p><p class="text-orange-300 text-xs mt-1">Perlu cek</p></div>
                        <div class="bg-white rounded-2xl p-4 text-gray-900 col-span-2">
                            <div class="flex justify-between items-center"><p class="font-semibold text-sm">Transaksi Terbaru</p><a href="/dashboard" class="text-blue-700 text-xs font-semibold">Buka →</a></div>
                            <div class="mt-3 space-y-2 text-sm">
                                <div class="flex justify-between"><span>Ahmad - 10 Mbps</span><span class="text-green-600 font-semibold">Lunas</span></div>
                                <div class="flex justify-between"><span>Budi - 20 Mbps</span><span class="text-green-600 font-semibold">Lunas</span></div>
                                <div class="flex justify-between"><span>Candra - 10 Mbps</span><span class="text-red-600 font-semibold">Belum</span></div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="absolute -bottom-6 -left-6 bg-white rounded-2xl shadow-xl p-4 hidden lg:flex items-center gap-3">
                    <div class="w-10 h-10 bg-green-100 rounded-xl flex items-center justify-center">✓</div>
                    <div><p class="font-semibold text-sm">Pembayaran Berhasil</p><p class="text-xs text-gray-500">Ahmad - Rp 150.000</p></div>
                </div>
            </div>
        </div>
    </section>

    <section id="fitur" class="bg-white py-16">
        <div class="max-w-7xl mx-auto px-6">
            <div class="text-center max-w-2xl mx-auto">
                <h3 class="text-2xl font-bold text-gray-900">Fitur Lengkap ISP</h3>
                <p class="text-gray-500 mt-2">Semua kebutuhan manajemen keuangan ISP dalam satu tempat</p>
            </div>
            <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-6 mt-10">
                <a href="/pelanggan" class="bg-gray-50 rounded-2xl p-6 hover:shadow-md transition text-left"><div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-xl">👥</div><h4 class="font-bold mt-4">Pelanggan</h4><p class="text-sm text-gray-500 mt-1">Kelola data pelanggan & status</p></a>
                <a href="/tagihan" class="bg-gray-50 rounded-2xl p-6 hover:shadow-md transition text-left"><div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-xl">🧾</div><h4 class="font-bold mt-4">Tagihan</h4><p class="text-sm text-gray-500 mt-1">Buat & pantau tagihan bulanan</p></a>
                <a href="/pembayaran" class="bg-gray-50 rounded-2xl p-6 hover:shadow-md transition text-left"><div class="w-12 h-12 bg-green-100 rounded-xl flex items-center justify-center text-xl">💳</div><h4 class="font-bold mt-4">Pembayaran</h4><p class="text-sm text-gray-500 mt-1">Catat pembayaran real-time</p></a>
                <a href="/laporan" class="bg-gray-50 rounded-2xl p-6 hover:shadow-md transition text-left"><div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-xl">📈</div><h4 class="font-bold mt-4">Laporan</h4><p class="text-sm text-gray-500 mt-1">Analisis pendapatan & tren</p></a>
            </div>
            <div class="text-center mt-10">
                <a href="/dashboard" class="inline-block bg-blue-900 text-white px-8 py-3.5 rounded-xl hover:bg-blue-800 transition font-semibold">Mulai Kelola Sekarang →</a>
            </div>
        </div>
    </section>

    <footer class="bg-blue-950 text-blue-200 py-8">
        <div class="max-w-7xl mx-auto px-6 flex flex-col md:flex-row justify-between items-center gap-2">
            <p class="text-sm">© 2026 SATAK - PT Nusa Data Koneksi Usaha</p>
            <div class="flex gap-4 text-sm"><a href="/dashboard" class="hover:text-white">Dashboard</a><a href="/paket" class="hover:text-white">Paket</a><a href="/pengaturan" class="hover:text-white">Pengaturan</a></div>
        </div>
    </footer>
</body>
</html>
