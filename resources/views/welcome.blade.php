<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SATAK Keuangan - Sistem Keuangan ISP</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 py-3 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
            <div class="flex items-center gap-3">
                <img src="{{ asset('satak.jpeg') }}" alt="Logo SATAK" class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl object-contain shadow-sm border border-slate-100">
                <div>
                    <h1 class="text-base sm:text-lg font-black tracking-tight bg-gradient-to-r from-[#00A896] via-[#0066FF] to-[#004BD6] bg-clip-text text-transparent leading-tight">SATAK</h1>
                    <p class="text-[10px] sm:text-[11px] font-semibold text-slate-400 tracking-wider">KONEK TERUS</p>
                </div>
            </div>
            <a href="/login" class="w-full sm:w-auto bg-slate-900 text-white px-4 py-2.5 rounded-lg hover:bg-slate-800 transition font-medium text-sm text-center">Masuk Dashboard</a>
        </div>
    </nav>

    <section class="relative w-full bg-cover bg-center" style="background-image: url('{{ asset('latar.jpeg') }}');">
        <div class="absolute inset-0 bg-slate-900/60"></div>
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 py-12 sm:py-16 lg:py-24 text-white">
            <span class="inline-block bg-white/20 text-white border border-white/30 px-3 py-1.5 rounded-full text-xs font-semibold mb-3 backdrop-blur-sm">PT Nusa Data Koneksi Usaha</span>
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-extrabold leading-tight tracking-tight max-w-xl">Kelola Keuangan ISP<br>Lebih Mudah</h2>
            <p class="mt-3 sm:mt-4 text-base sm:text-lg leading-relaxed text-white/90 max-w-lg">SATAK bantu kelola pelanggan, tagihan, pembayaran, dan laporan keuangan dalam satu dashboard terintegrasi.</p>
            <div class="flex flex-col sm:flex-row gap-3 mt-6 sm:mt-8">
                <a href="/dashboard" class="bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] hover:from-[#00B8A8] hover:via-[#0059C7] hover:to-[#0046B4] text-white px-6 py-3 rounded-lg transition font-semibold shadow-md text-center">Masuk Dashboard</a>
                <a href="#fitur" class="bg-white/10 backdrop-blur border border-white/20 text-white px-6 py-3 rounded-lg hover:bg-white/20 transition font-semibold text-center">Lihat Fitur</a>
            </div>
            <div class="flex flex-wrap justify-center sm:justify-start gap-6 mt-8 sm:mt-10">
                <div><p class="text-2xl font-bold text-white">125+</p><p class="text-xs sm:text-sm text-white/80">Pelanggan</p></div>
                <div><p class="text-2xl font-bold text-white">99%</p><p class="text-xs sm:text-sm text-white/80">Akurasi</p></div>
                <div><p class="text-2xl font-bold text-white">24/7</p><p class="text-xs sm:text-sm text-white/80">Monitoring</p></div>
            </div>
        </div>
    </section>

    <section id="fitur" class="bg-white border-y border-slate-200 py-12 sm:py-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6">
            <div class="text-center max-w-2xl mx-auto">
                <span class="text-xs font-semibold text-slate-500 uppercase tracking-wider">Pilihan Layanan</span>
                <h3 class="text-2xl sm:text-3xl font-bold text-slate-900 mt-2 tracking-tight">Pilihan Paket Internet</h3>
                <p class="text-slate-600 mt-2 text-sm sm:text-base">Daftar paket internet cepat, stabil, dan terjangkau untuk kebutuhan rumah hingga bisnis.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4 sm:gap-6 mt-10 sm:mt-12">
                <div class="bg-white rounded-xl p-5 sm:p-7 border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <span class="inline-block bg-slate-100 text-slate-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 sm:mb-4">Rumah Tangga</span>
                        <h4 class="text-lg sm:text-xl font-bold text-slate-900">Paket Hemat</h4>
                        <p class="text-slate-600 text-xs sm:text-sm mt-2">Cocok untuk browsing harian, media sosial, dan chat.</p>
                        <div class="mt-4 sm:mt-6">
                            <span class="text-2xl sm:text-3xl font-bold text-slate-900">Rp 150.000</span>
                            <span class="text-slate-500 text-xs sm:text-sm">/bulan</span>
                        </div>
                        <ul class="mt-4 sm:mt-6 space-y-2 sm:space-y-3 text-xs sm:text-sm text-slate-600">
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Kecepatan hingga 10 Mbps</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Kuota Unlimited (Tanpa FUP)</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Ideal untuk 1 - 3 perangkat</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Dukungan teknis 24/7</li>
                        </ul>
                    </div>
                    <a href="/login" class="mt-5 sm:mt-8 block text-center bg-white border border-slate-300 text-slate-800 hover:bg-slate-50 font-semibold py-2.5 sm:py-3 rounded-lg transition text-sm">Pilih Paket</a>
                </div>

                <div class="bg-slate-900 text-white rounded-xl p-5 sm:p-7 shadow-md flex flex-col justify-between relative">
                    <span class="absolute -top-2 right-4 sm:-top-3 sm:right-6 bg-gradient-to-r from-[#00D2B4] to-[#0066FF] text-white text-xs font-bold px-3 py-1 rounded-full uppercase tracking-wider">Terpopuler</span>
                    <div>
                        <span class="inline-block bg-white/10 text-white text-xs font-semibold px-3 py-1 rounded-full mb-3 sm:mb-4">Keluarga</span>
                        <h4 class="text-lg sm:text-xl font-bold">Paket Reguler</h4>
                        <p class="text-slate-300 text-xs sm:text-sm mt-2">Ideal untuk streaming video HD, meeting online, dan belajar daring.</p>
                        <div class="mt-4 sm:mt-6">
                            <span class="text-2xl sm:text-3xl font-bold">Rp 250.000</span>
                            <span class="text-slate-300 text-xs sm:text-sm">/bulan</span>
                        </div>
                        <ul class="mt-4 sm:mt-6 space-y-2 sm:space-y-3 text-xs sm:text-sm text-slate-200">
                            <li class="flex items-center gap-2"><span class="font-bold">✓</span> Kecepatan hingga 20 Mbps</li>
                            <li class="flex items-center gap-2"><span class="font-bold">✓</span> Kuota Unlimited (Tanpa FUP)</li>
                            <li class="flex items-center gap-2"><span class="font-bold">✓</span> Ideal untuk 4 - 7 perangkat</li>
                            <li class="flex items-center gap-2"><span class="font-bold">✓</span> Bebas biaya instalasi</li>
                        </ul>
                    </div>
                    <a href="/login" class="mt-5 sm:mt-8 block text-center bg-white text-slate-900 hover:bg-slate-100 font-semibold py-2.5 sm:py-3 rounded-lg transition text-sm">Pilih Paket</a>
                </div>

                <div class="bg-white rounded-xl p-5 sm:p-7 border border-slate-200 shadow-sm flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <span class="inline-block bg-slate-100 text-slate-700 text-xs font-semibold px-3 py-1 rounded-full mb-3 sm:mb-4">Bisnis / Kantor</span>
                        <h4 class="text-lg sm:text-xl font-bold text-slate-900">Paket Pro</h4>
                        <p class="text-slate-600 text-xs sm:text-sm mt-2">Koneksi stabil dan cepat untuk operasional kantor & kafe.</p>
                        <div class="mt-4 sm:mt-6">
                            <span class="text-2xl sm:text-3xl font-bold text-slate-900">Rp 450.000</span>
                            <span class="text-slate-500 text-xs sm:text-sm">/bulan</span>
                        </div>
                        <ul class="mt-4 sm:mt-6 space-y-2 sm:space-y-3 text-xs sm:text-sm text-slate-600">
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Kecepatan hingga 50 Mbps</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Prioritas bandwidth ratio 1:1</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Ideal untuk 8+ perangkat</li>
                            <li class="flex items-center gap-2"><span class="text-emerald-600 font-bold">✓</span> Dukungan teknis prioritas</li>
                        </ul>
                    </div>
                    <a href="/login" class="mt-5 sm:mt-8 block text-center bg-white border border-slate-300 text-slate-800 hover:bg-slate-50 font-semibold py-2.5 sm:py-3 rounded-lg transition text-sm">Pilih Paket</a>
                </div>
            </div>

            <div class="text-center mt-10 sm:mt-12">
                <a href="/paket" class="text-blue-600 hover:text-blue-700 font-semibold text-sm">Kelola Master Paket di Dashboard</a>
            </div>
        </div>
    </section>

    <footer class="bg-slate-900 text-slate-400 py-6 sm:py-8">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 flex flex-col sm:flex-row justify-between items-center gap-3 sm:gap-2">
            <p class="text-xs sm:text-sm text-center">&copy; 2026 SATAK - PT Nusa Data Koneksi Usaha</p>
            <div class="flex flex-wrap justify-center gap-3 sm:gap-4 text-xs sm:text-sm"><a href="/dashboard" class="hover:text-white">Dashboard</a><a href="/paket" class="hover:text-white">Paket</a><a href="/pengaturan" class="hover:text-white">Pengaturan</a></div>
        </div>
    </footer>
</body>
</html>
