<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pelanggan | SATAK</title>
    <!-- Ganti script CDN di bawah ini dengan @vite(['resources/css/app.css', 'resources/js/app.js']) jika masuk ke Laravel -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F1F5F9] text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-60 bg-white flex-col h-full border-r border-slate-200 shrink-0 hidden md:flex">
        <!-- Logo -->
        <div class="h-16 flex items-center px-5 gap-3 border-b border-slate-100">
            <div class="w-9 h-9 bg-[#2563EB] rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
            </div>
            <div>
                <p class="font-extrabold text-slate-900 text-[15px] leading-tight tracking-wide">SATAK</p>
                <p class="text-[11px] text-slate-400 font-medium">Solusi Internet</p>
            </div>
        </div>

        <!-- Menu Navigasi -->
        <div class="px-4 py-5 flex-1">
            <p class="px-3 text-[10px] font-bold text-slate-400 mb-3 tracking-widest uppercase">Menu</p>
            <nav class="space-y-1">
                <a href="#" class="flex items-center gap-3 bg-[#2563EB] text-white px-3 py-2.5 rounded-xl font-semibold text-sm shadow-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Dashboard
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Tagihan Saya
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Pembayaran
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                    Paket Internet
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-500 hover:text-slate-900 hover:bg-slate-100 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    Pengaturan
                </a>
            </nav>
        </div>

        <!-- Bawah: Keluar -->
        <div class="p-4 border-t border-slate-100">
            <a href="#" class="flex items-center gap-3 bg-slate-900 hover:bg-slate-800 text-white px-3 py-2.5 rounded-xl font-semibold text-sm transition">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                Keluar
            </a>
        </div>
    </aside>

    <!-- Main Layout -->
    <main class="flex-1 flex flex-col h-full overflow-hidden">
        <!-- Header Top -->
        <header class="h-16 bg-white border-b border-slate-200 flex items-center justify-between px-6 lg:px-8 shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-900 leading-tight">Dashboard</h1>
                <p class="text-[11px] text-slate-400 font-medium">Pantau paket, tagihan, dan pembayaran kamu</p>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <div class="absolute top-1 right-1.5 w-2 h-2 bg-red-500 rounded-full border-2 border-white"></div>
                </div>
                <div class="flex items-center gap-3 pl-5 border-l border-slate-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-[13px] font-bold text-slate-900 leading-tight">Andi</p>
                        <p class="text-[11px] text-slate-400">Pelanggan</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-[#2563EB] text-white flex items-center justify-center font-bold text-sm">A</div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="flex-1 overflow-y-auto p-6 lg:p-8">
            <div class="max-w-7xl mx-auto space-y-6 pb-8">

                <!-- Banner Sambutan -->
                <div class="bg-gradient-to-r from-[#1D4ED8] via-[#2563EB] to-[#3B82F6] rounded-2xl px-7 py-6 text-white relative overflow-hidden shadow-lg shadow-blue-600/20">
                    <div class="absolute -right-10 -top-16 w-56 h-56 bg-white/10 rounded-full"></div>
                    <div class="absolute right-24 -bottom-20 w-40 h-40 bg-white/10 rounded-full"></div>
                    <div class="relative">
                        <h2 class="text-xl font-bold">Selamat Datang di SATAK</h2>
                        <p class="text-blue-100 text-sm mt-1">Kelola paket, tagihan, dan riwayat pembayaran kamu melalui satu portal.</p>
                    </div>
                </div>

                <!-- Statistik: 4 Kartu -->
                <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-5">
                    <!-- Card 1 -->
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm">
                        <p class="text-xs font-medium text-slate-400">Paket Aktif</p>
                        <p class="text-[26px] font-extrabold text-slate-900 tracking-tight mt-1.5">10 Mbps</p>
                        <p class="text-[11px] font-semibold text-emerald-600 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>
                            99,8% uptime bulan ini
                        </p>
                    </div>
                    <!-- Card 2 -->
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm">
                        <p class="text-xs font-medium text-slate-400">Tagihan Bulan Ini</p>
                        <p class="text-[26px] font-extrabold text-slate-900 tracking-tight mt-1.5">Rp 150.000</p>
                        <p class="text-[11px] font-semibold text-slate-400 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            Jatuh tempo 10 Okt 2026
                        </p>
                    </div>
                    <!-- Card 3 -->
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm">
                        <p class="text-xs font-medium text-slate-400">Tagihan Belum Dibayar</p>
                        <p class="text-[26px] font-extrabold text-slate-900 tracking-tight mt-1.5">1</p>
                        <p class="text-[11px] font-semibold text-red-500 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            Perlu ditindaklanjuti
                        </p>
                    </div>
                    <!-- Card 4 -->
                    <div class="bg-white rounded-2xl border border-slate-200/70 p-5 shadow-sm">
                        <p class="text-xs font-medium text-slate-400">Total Pembayaran</p>
                        <p class="text-[26px] font-extrabold text-slate-900 tracking-tight mt-1.5">Rp 600.000</p>
                        <p class="text-[11px] font-semibold text-emerald-600 mt-2 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 15l7-7 7 7"></path></svg>
                            4 bulan terakhir
                        </p>
                    </div>
                </div>

                <!-- Layout Bawah (Tabel & Status) -->
                <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

                    <!-- Transaksi Terbaru (Span 2 Kolom) -->
                    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm lg:col-span-2 overflow-hidden flex flex-col">
                        <div class="px-6 py-5 border-b border-slate-100 flex justify-between items-center">
                            <div>
                                <h3 class="font-bold text-lg text-slate-900">Transaksi Terbaru</h3>
                                <p class="text-xs text-slate-400 mt-0.5">Riwayat pembayaran terakhir kamu</p>
                            </div>
                            <a href="#" class="text-xs font-bold text-[#2563EB] hover:underline">Lihat Semua</a>
                        </div>
                        <div class="flex-1 overflow-x-auto">
                            <table class="w-full text-left border-collapse">
                                <thead>
                                    <tr class="text-[11px] text-slate-400 uppercase tracking-wider font-bold border-b border-slate-100">
                                        <th class="px-6 py-3">Periode</th>
                                        <th class="px-6 py-3">Metode</th>
                                        <th class="px-6 py-3">Jumlah</th>
                                        <th class="px-6 py-3">Status</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <!-- Baris: September -->
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition">
                                        <td class="px-6 py-4 font-semibold text-[13px] text-slate-900">September 2026</td>
                                        <td class="px-6 py-4 text-[13px] text-slate-500">Transfer Bank</td>
                                        <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                        <td class="px-6 py-4">
                                            <span class="bg-[#FFFBEB] text-[#D97706] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#FEF3C7]">
                                                <div class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></div>
                                                Belum Bayar
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- Baris: Agustus -->
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition">
                                        <td class="px-6 py-4 font-semibold text-[13px] text-slate-900">Agustus 2026</td>
                                        <td class="px-6 py-4 text-[13px] text-slate-500">QRIS</td>
                                        <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                        <td class="px-6 py-4">
                                            <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                                <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                                Lunas
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- Baris: Juli -->
                                    <tr class="border-b border-slate-50 hover:bg-slate-50/60 transition">
                                        <td class="px-6 py-4 font-semibold text-[13px] text-slate-900">Juli 2026</td>
                                        <td class="px-6 py-4 text-[13px] text-slate-500">Transfer Bank</td>
                                        <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                        <td class="px-6 py-4">
                                            <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                                <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                                Lunas
                                            </span>
                                        </td>
                                    </tr>
                                    <!-- Baris: Juni -->
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="px-6 py-4 font-semibold text-[13px] text-slate-900">Juni 2026</td>
                                        <td class="px-6 py-4 text-[13px] text-slate-500">QRIS</td>
                                        <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                        <td class="px-6 py-4">
                                            <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                                <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                                Lunas
                                            </span>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Status Layanan -->
                    <div class="bg-white rounded-2xl border border-slate-200/70 shadow-sm p-6 flex flex-col">
                        <div class="mb-6">
                            <h3 class="font-bold text-lg text-slate-900">Status Layanan</h3>
                            <p class="text-xs text-slate-400 mt-0.5">Kondisi layanan kamu saat ini</p>
                        </div>

                        <div class="space-y-6 flex-1">
                            <!-- Download -->
                            <div>
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <span class="font-semibold text-slate-600">Download</span>
                                    <span class="font-bold text-slate-900">10 Mbps</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-[#2563EB] h-full rounded-full" style="width: 100%"></div>
                                </div>
                            </div>
                            <!-- Upload -->
                            <div>
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <span class="font-semibold text-slate-600">Upload</span>
                                    <span class="font-bold text-slate-900">5 Mbps</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-[#60A5FA] h-full rounded-full" style="width: 50%"></div>
                                </div>
                            </div>
                            <!-- Uptime -->
                            <div>
                                <div class="flex justify-between items-center text-xs mb-2">
                                    <span class="font-semibold text-slate-600">Uptime</span>
                                    <span class="font-bold text-slate-900">99,8%</span>
                                </div>
                                <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                                    <div class="bg-[#10B981] h-full rounded-full" style="width: 99%"></div>
                                </div>
                            </div>
                        </div>

                        <!-- Indikator Status -->
                        <div class="mt-6 bg-[#F0FDF4] border border-[#D1FAE5] rounded-xl px-4 py-3 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-[#10B981] flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                            <div>
                                <p class="text-xs font-bold text-[#047857]">Layanan Aktif</p>
                                <p class="text-[11px] text-[#059669]">Koneksi berjalan normal</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

</body>
</html>
