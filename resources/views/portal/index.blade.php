<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Portal Pelanggan | SATAK</title>
    <!-- Ganti script CDN di bawah ini dengan @vite(['resources/css/app.css', 'resources/js/app.js']) jika masuk ke Laravel -->
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-[#F8FAFC] text-slate-800 antialiased flex h-screen overflow-hidden">

    <!-- Sidebar -->
    <aside class="w-64 bg-[#111827] flex flex-col h-full border-r border-slate-800 shrink-0">
        <!-- Logo -->
        <div class="h-[72px] flex items-center px-6 gap-3">
            <div class="bg-[#00D4FF] p-1.5 rounded-lg flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-slate-900" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
            </div>
            <div>
                <p class="font-bold text-white text-[13px] leading-tight tracking-wide">SATAK</p>
                <p class="text-[11px] text-slate-400 font-medium">Portal Pelanggan</p>
            </div>
        </div>

        <!-- Profil Mini Sidebar -->
        <div class="px-4 py-3">
            <div class="bg-[#1F2937] rounded-xl p-3 flex items-center gap-3 border border-slate-700/50">
                <div class="w-9 h-9 rounded-full bg-[#0EA5E9] text-white flex items-center justify-center font-bold text-sm relative shrink-0">
                    A
                    <div class="absolute bottom-0 right-0 w-2.5 h-2.5 bg-[#10B981] rounded-full border-2 border-[#1F2937]"></div>
                </div>
                <div>
                    <p class="text-sm font-semibold text-white leading-tight">Andi</p>
                    <p class="text-[11px] text-slate-400 mt-0.5 font-mono">PLG-0001</p>
                </div>
            </div>
        </div>

        <!-- Menu Navigasi -->
        <div class="px-4 py-4 flex-1">
            <p class="px-3 text-[10px] font-bold text-slate-500 mb-3 tracking-widest uppercase">Menu</p>
            <nav class="space-y-1">
                <a href="#" class="flex items-center gap-3 bg-[#00E0FF] text-slate-900 px-3 py-2.5 rounded-xl font-bold text-sm">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
                    Ringkasan
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-slate-800/50 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                    Tagihan Saya
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-slate-800/50 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    Pembayaran
                </a>
                <a href="#" class="flex items-center gap-3 text-slate-400 hover:text-white hover:bg-slate-800/50 px-3 py-2.5 rounded-xl font-medium text-sm transition">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    Profil Saya
                </a>
            </nav>
        </div>

        <!-- Info Paket & Bantuan Bawah Sidebar -->
        <div class="p-4 space-y-3">
            <div class="bg-[#0F172A] border border-slate-700/50 rounded-xl p-4">
                <p class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Paket Aktif</p>
                <p class="text-sm font-bold text-white mt-1">10 Mbps</p>
                <p class="text-[11px] text-slate-500 mt-0.5">Paket Rumah Hemat</p>
                <div class="flex items-center gap-1.5 mt-3">
                    <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                    <span class="text-[11px] font-semibold text-[#10B981]">Koneksi Normal</span>
                </div>
            </div>
            <div class="bg-[#0F172A] border border-slate-700/50 rounded-xl p-4">
                <div class="flex items-start gap-3 mb-4">
                    <div class="w-8 h-8 rounded-lg bg-[#1E293B] flex items-center justify-center text-slate-300 font-bold shrink-0 text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"></path></svg>
                    </div>
                    <div>
                        <p class="text-xs font-semibold text-white">Butuh bantuan?</p>
                        <p class="text-[11px] text-slate-400 mt-0.5 leading-relaxed">Hubungi admin ISP kami</p>
                    </div>
                </div>
                <button class="w-full py-2 bg-transparent border border-slate-700 rounded-lg text-[11px] font-semibold text-slate-300 hover:bg-slate-800 transition">Hubungi Sekarang</button>
            </div>
        </div>
    </aside>

    <!-- Main Layout -->
    <main class="flex-1 flex flex-col h-full relative overflow-y-auto w-full">
        <!-- Header Top -->
        <header class="h-[72px] flex items-center justify-between px-8 bg-white border-b border-slate-100 shrink-0 sticky top-0 z-10">
            <div>
                <p class="text-[11px] font-medium text-slate-400 mb-0.5">Sabtu, 26 September 2026</p>
                <h1 class="text-lg font-bold text-slate-900">Halo, Andi <span class="text-xl inline-block ml-1">👋</span></h1>
            </div>
            <div class="flex items-center gap-5">
                <div class="relative text-slate-400 hover:text-slate-600 cursor-pointer p-1">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"></path></svg>
                    <div class="absolute top-1 right-1.5 w-2 h-2 bg-[#F59E0B] rounded-full border-2 border-white"></div>
                </div>
                <div class="flex items-center gap-3 pl-5 border-l border-slate-200">
                    <div class="text-right hidden sm:block">
                        <p class="text-[13px] font-bold text-slate-900 leading-tight">Andi</p>
                        <p class="text-[11px] text-slate-500">Pelanggan</p>
                    </div>
                    <div class="w-9 h-9 rounded-full bg-[#0EA5E9] text-white flex items-center justify-center font-bold text-sm">A</div>
                </div>
            </div>
        </header>

        <!-- Main Content Area -->
        <div class="p-8 space-y-6 max-w-7xl w-full mx-auto pb-20">
            
            <!-- Hero Card: Status Layanan -->
            <div class="bg-[#0B1120] rounded-[20px] p-8 text-white relative overflow-hidden shadow-lg flex flex-col md:flex-row justify-between items-start md:items-center gap-8">
                <!-- Left Content -->
                <div class="z-10 w-full md:w-3/5">
                    <p class="text-[10px] font-bold tracking-[0.2em] text-[#00E0FF] uppercase mb-3">Status Layanan</p>
                    <h2 class="text-[28px] font-bold mb-3">Internet kamu aktif</h2>
                    <p class="text-slate-400 text-sm leading-relaxed max-w-md mb-8">Koneksi berjalan normal. Kamu dapat memantau paket, tagihan, dan riwayat pembayaran dari halaman ini.</p>
                    
                    <div class="grid grid-cols-4 gap-6 pt-2">
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-1.5">Kecepatan</p>
                            <p class="font-bold text-white font-mono text-sm">10 Mbps</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-1.5">Uptime</p>
                            <p class="font-bold text-white font-mono text-sm">99.8%</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-1.5">Aktif Sejak</p>
                            <p class="font-bold text-white font-mono text-sm">Jan 2025</p>
                        </div>
                        <div>
                            <p class="text-[10px] text-slate-500 uppercase font-bold tracking-wider mb-1.5">Lokasi</p>
                            <p class="font-bold text-white font-mono text-sm">Jl. Raya No. 1</p>
                        </div>
                    </div>
                </div>

                <!-- Right Content (Speedbars) -->
                <div class="z-10 w-full md:w-80 flex flex-col items-end">
                    <div class="bg-[#064E3B] border border-[#047857] px-3.5 py-1.5 rounded-full flex items-center gap-2 mb-6">
                        <div class="w-1.5 h-1.5 bg-[#34D399] rounded-full"></div>
                        <span class="text-xs text-[#34D399] font-bold">Aktif</span>
                    </div>
                    
                    <div class="w-full bg-[#111827] border border-slate-800 rounded-xl p-5 space-y-4">
                        <div>
                            <div class="flex justify-between text-[11px] mb-2 font-mono">
                                <span class="text-slate-400">Download</span>
                                <span class="text-[#00E0FF] font-bold">10 Mbps</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-[#00E0FF] h-full w-full rounded-full"></div>
                            </div>
                        </div>
                        <div>
                            <div class="flex justify-between text-[11px] mb-2 font-mono">
                                <span class="text-slate-400">Upload</span>
                                <span class="text-[#818CF8] font-bold">5 Mbps</span>
                            </div>
                            <div class="w-full bg-slate-800 h-1.5 rounded-full overflow-hidden">
                                <div class="bg-[#818CF8] h-full w-1/2 rounded-full"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Grid 3 Kartu Info -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Card 1 -->
                <div class="bg-white rounded-[20px] p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-full bg-[#EFF6FF] flex items-center justify-center text-[#3B82F6] mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.857 15.355-5.857 21.213 0"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-slate-500 mb-1">Paket saat ini</p>
                        <p class="text-2xl font-bold text-slate-900 tracking-tight">10 Mbps</p>
                        <p class="text-[11px] text-slate-400 mt-1.5 font-medium">Paket Rumah Hemat</p>
                    </div>
                </div>
                
                <!-- Card 2 -->
                <div class="bg-white rounded-[20px] p-6 border border-slate-100 shadow-sm flex flex-col justify-between relative overflow-hidden">
                    <div class="w-10 h-10 rounded-full bg-[#FFFBEB] flex items-center justify-center text-[#F59E0B] mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-slate-500 mb-1">Tagihan September</p>
                        <p class="text-2xl font-bold text-slate-900 tracking-tight">Rp 150.000</p>
                        <div class="flex items-center gap-3 mt-1.5">
                             <p class="text-[11px] text-[#F59E0B] font-bold">Jatuh tempo 10 Oktober 2026</p>
                        </div>
                    </div>
                    <!-- Badge status dalam card -->
                    <div class="mt-4">
                         <span class="bg-[#FEF3C7] text-[#D97706] text-[10px] font-bold px-2 py-1 rounded">Belum Bayar</span>
                    </div>
                </div>

                <!-- Card 3 -->
                <div class="bg-white rounded-[20px] p-6 border border-slate-100 shadow-sm flex flex-col justify-between">
                    <div class="w-10 h-10 rounded-full bg-[#F0FDF4] flex items-center justify-center text-[#10B981] mb-5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                    </div>
                    <div>
                        <p class="text-[11px] font-medium text-slate-500 mb-1">Nomor Pelanggan</p>
                        <p class="text-2xl font-bold text-slate-900 tracking-tight">PLG-0001</p>
                        <p class="text-[11px] text-slate-400 mt-1.5 font-medium">Terdaftar sejak Januari 2025</p>
                    </div>
                </div>
            </div>

            <!-- Banner Peringatan -->
            <div class="bg-[#FFFBEB] border border-[#FDE68A] rounded-[16px] p-5 flex flex-col sm:flex-row items-start sm:items-center justify-between shadow-sm gap-4">
                <div class="flex items-start gap-4">
                    <div class="text-[#F59E0B] bg-[#FEF3C7] p-1.5 rounded-full shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                    <div>
                        <h4 class="font-bold text-[#92400E] text-sm">Tagihan September belum dibayar</h4>
                        <p class="text-[#B45309] text-[13px] mt-1 leading-snug">Tagihan sebesar <strong>Rp 150.000</strong> jatuh tempo <strong>10 Oktober 2026</strong>. Bayar sebelum jatuh tempo untuk menghindari pemutusan koneksi.</p>
                    </div>
                </div>
                <button class="w-full sm:w-auto bg-[#F59E0B] hover:bg-[#D97706] text-white px-6 py-2.5 rounded-xl text-sm font-bold whitespace-nowrap transition-colors shadow-sm">
                    Bayar Sekarang
                </button>
            </div>

            <!-- Layout Bawah (Tabel & Profil) -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <!-- Riwayat Pembayaran (Span 2 Kolom) -->
                <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm lg:col-span-2 overflow-hidden flex flex-col">
                    <div class="p-6 border-b border-slate-100 flex justify-between items-center">
                        <div>
                            <h3 class="font-bold text-lg text-slate-900">Riwayat Pembayaran</h3>
                            <p class="text-xs text-slate-500 mt-0.5">5 tagihan terakhir</p>
                        </div>
                        <button class="text-xs border border-slate-200 rounded-lg px-3 py-1.5 text-slate-600 font-semibold hover:bg-slate-50 transition">Lihat semua &rarr;</button>
                    </div>
                    <div class="flex-1 overflow-x-auto">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="text-[10px] text-slate-400 uppercase tracking-widest font-bold">
                                    <th class="px-6 py-4">Periode</th>
                                    <th class="px-6 py-4">Tanggal Bayar</th>
                                    <th class="px-6 py-4">Nominal</th>
                                    <th class="px-6 py-4">Status</th>
                                </tr>
                            </thead>
                            <tbody>
                                <!-- Baris: September -->
                                <tr class="border-t border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-[13px] text-slate-900">September 2026</td>
                                    <td class="px-6 py-4 text-[13px] text-slate-400 font-mono">&mdash;</td>
                                    <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#FFFBEB] text-[#D97706] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#FEF3C7]">
                                            <div class="w-1.5 h-1.5 rounded-full bg-[#F59E0B]"></div>
                                            Belum Bayar
                                        </span>
                                    </td>
                                </tr>
                                <!-- Baris: Agustus -->
                                <tr class="border-t border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-[13px] text-slate-900">Agustus 2026</td>
                                    <td class="px-6 py-4 text-[13px] text-slate-500 font-mono">05 Sep 2026</td>
                                    <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                            <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                            Lunas
                                        </span>
                                    </td>
                                </tr>
                                <!-- Baris: Juli -->
                                <tr class="border-t border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-[13px] text-slate-900">Juli 2026</td>
                                    <td class="px-6 py-4 text-[13px] text-slate-500 font-mono">08 Agu 2026</td>
                                    <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                            <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                            Lunas
                                        </span>
                                    </td>
                                </tr>
                                <!-- Baris: Juni -->
                                <tr class="border-t border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-[13px] text-slate-900">Juni 2026</td>
                                    <td class="px-6 py-4 text-[13px] text-slate-500 font-mono">07 Jul 2026</td>
                                    <td class="px-6 py-4 text-[13px] font-bold text-slate-900 font-mono">Rp 150.000</td>
                                    <td class="px-6 py-4">
                                        <span class="bg-[#F0FDF4] text-[#059669] flex items-center w-fit gap-1.5 px-2.5 py-1 rounded-full text-[11px] font-bold border border-[#D1FAE5]">
                                            <div class="w-1.5 h-1.5 rounded-full bg-[#10B981]"></div>
                                            Lunas
                                        </span>
                                    </td>
                                </tr>
                                <!-- Baris: Mei -->
                                <tr class="border-t border-slate-100 hover:bg-slate-50/50 transition">
                                    <td class="px-6 py-4 font-bold text-[13px] text-slate-900">Mei 2026</td>
                                    <td class="px-6 py-4 text-[13px] text-slate-500 font-mono">05 Jun 2026</td>
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
                    <!-- Footer Tabel -->
                    <div class="px-6 py-4 border-t border-slate-100 flex justify-between items-center bg-slate-50/50 mt-auto">
                        <p class="text-[11px] text-slate-500 font-medium">Total terbayar: <span class="font-bold text-slate-800 font-mono ml-1 text-xs">Rp 600.000</span></p>
                        <a href="#" class="text-[#3B82F6] text-xs font-bold hover:underline">Download PDF</a>
                    </div>
                </div>

                <!-- Kartu Profil Saya -->
                <div class="bg-white rounded-[20px] border border-slate-100 shadow-sm p-6 flex flex-col h-full">
                    <h3 class="font-bold text-lg text-slate-900 mb-6">Profil Saya</h3>

                    <!-- Header Profil -->
                    <div class="flex items-center gap-4 mb-8 bg-[#F8FAFC] p-4 rounded-2xl border border-slate-100">
                        <div class="w-14 h-14 rounded-full bg-[#0EA5E9] text-white flex items-center justify-center text-xl font-bold relative shrink-0">
                            A
                            <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-[#10B981] rounded-full border-[3px] border-[#F8FAFC] flex items-center justify-center">
                                <svg class="w-2.5 h-2.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="4" d="M5 13l4 4L19 7"></path></svg>
                            </div>
                        </div>
                        <div>
                            <p class="font-bold text-slate-900 text-lg leading-tight">Andi</p>
                            <p class="text-[11px] text-slate-500 mb-1">Pelanggan Aktif</p>
                            <p class="text-[11px] font-bold text-[#0EA5E9] font-mono">PLG-0001</p>
                        </div>
                    </div>

                    <!-- Detail Profil -->
                    <div class="space-y-5 flex-1 px-1">
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nama Lengkap</p>
                            <p class="text-[13px] font-semibold text-slate-900">Andi</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Nomor HP</p>
                            <p class="text-[13px] font-semibold text-slate-900 font-mono">0812-3456-789</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Alamat</p>
                            <p class="text-[13px] font-semibold text-slate-900">Jl. Raya No. 1</p>
                        </div>
                        <div>
                            <p class="text-[10px] font-bold text-slate-400 uppercase tracking-widest mb-1.5">Email</p>
                            <p class="text-[13px] font-semibold text-slate-900">amri@email.com</p>
                        </div>
                    </div>

                    <button class="w-full bg-[#0B1120] text-white py-3 rounded-xl font-bold text-[13px] hover:bg-slate-800 transition shadow-md mt-8">
                        Edit Profil
                    </button>
                </div>
            </div>
        </div>
    </main>

    <!-- Tombol Bantuan Mengambang (Pojok Kanan Bawah) -->
    <button class="fixed bottom-6 right-6 w-12 h-12 bg-[#1E293B] hover:bg-black rounded-full flex items-center justify-center text-white text-xl font-bold shadow-xl transition-transform hover:scale-110 z-50">
        ?
    </button>

</body>
</html>