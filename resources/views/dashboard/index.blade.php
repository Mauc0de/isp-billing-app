@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Dashboard</h2>
        <p class="text-gray-500 text-xs md:text-sm">Ringkasan keuangan dan pelanggan ISP</p>
    </div>
    <div class="flex items-center gap-3">
        <div class="text-right">
            <p class="font-semibold text-sm text-gray-800">{{ auth()->user()->name }}</p>
            <p class="text-xs text-gray-500">Administrator</p>
        </div>
        <div class="w-9 h-9 md:w-10 md:h-10 rounded-full bg-blue-800 text-white flex items-center justify-center font-bold">
            {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
        </div>
    </div>
</header>
<div class="p-4 md:p-8">
    <div>
        <div class="bg-gradient-to-r from-blue-800 to-blue-700 rounded-2xl p-5 md:p-7 text-white mb-6 md:mb-8 shadow-sm">
            <h2 class="text-lg md:text-2xl font-bold mb-1 md:mb-2">Selamat Datang di SATAK</h2>
            <p class="text-blue-100 text-sm md:text-base">Kelola pelanggan, pembayaran, tagihan dan keuangan ISP melalui satu dashboard.</p>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
            <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
                <p class="text-gray-500 text-sm">Total Pelanggan</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mt-2">{{ $totalPelanggan }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
                <p class="text-gray-500 text-sm">Paket Internet</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mt-2">{{ $totalPaket }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
                <p class="text-gray-500 text-sm">Tagihan Belum Dibayar</p>
                <h3 class="text-2xl md:text-3xl font-bold text-gray-800 mt-2">{{ $tagihanBelumBayar }}</h3>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
                <p class="text-gray-500 text-sm">Pendapatan Bulan Ini</p>
                <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-2 break-words">Rp {{ number_format($pembayaranBulanIni, 0, ',', '.') }}</h3>
            </div>
        </div>
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-4 md:gap-6">
            <div class="xl:col-span-2 bg-white rounded-2xl shadow-sm overflow-hidden">
                <div class="p-5 md:p-6 border-b flex justify-between items-center gap-3">
                    <div>
                        <h3 class="font-bold text-base md:text-lg">Transaksi Terbaru</h3>
                        <p class="text-gray-500 text-xs md:text-sm">Pembayaran pelanggan terbaru</p>
                    </div>
                    <a href="/pembayaran" class="text-blue-700 text-xs md:text-sm font-semibold whitespace-nowrap">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto -mx-0">
                    <table class="w-full min-w-[520px] text-sm">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="text-left p-4 text-gray-500">Pelanggan</th>
                                <th class="text-left p-4 text-gray-500">Jumlah</th>
                                <th class="text-left p-4 text-gray-500">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse(\App\Models\Pembayaran::with('pelanggan')->latest()->take(5)->get() as $pembayaran)
                            <tr class="border-t">
                                <td class="p-4 font-medium">{{ $pembayaran->pelanggan->nama ?? '-' }}</td>
                                <td class="p-4 whitespace-nowrap">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                                <td class="p-4">
                                    <span class="bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs whitespace-nowrap">
                                        {{ $pembayaran->status }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="3" class="p-4 text-center text-gray-400">Belum ada transaksi</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
                <h3 class="font-bold text-base md:text-lg">Status Pelanggan</h3>
                <p class="text-gray-500 text-xs md:text-sm mb-6">Kondisi pelanggan saat ini</p>
                <div class="space-y-5">
                    @php
                        $totalPelanggan = $totalPelanggan > 0 ? $totalPelanggan : 1;
                        $aktif = \App\Models\Pelanggan::where('status', 'aktif')->count();
                        $menunggak = \App\Models\Pelanggan::where('status', 'menunggak')->count();
                        $nonaktif = \App\Models\Pelanggan::where('status', 'nonaktif')->count();
                    @endphp
                    <div>
                        <div class="flex justify-between mb-2"><span class="text-sm">Aktif</span><span class="font-semibold">{{ $aktif }}</span></div>
                        <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-green-500 h-2 rounded-full" style="width: {{ round($aktif / $totalPelanggan * 100) }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2"><span class="text-sm">Menunggak</span><span class="font-semibold">{{ $menunggak }}</span></div>
                        <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-red-500 h-2 rounded-full" style="width: {{ round($menunggak / $totalPelanggan * 100) }}%"></div></div>
                    </div>
                    <div>
                        <div class="flex justify-between mb-2"><span class="text-sm">Nonaktif</span><span class="font-semibold">{{ $nonaktif }}</span></div>
                        <div class="w-full bg-gray-200 rounded-full h-2"><div class="bg-gray-500 h-2 rounded-full" style="width: {{ round($nonaktif / $totalPelanggan * 100) }}%"></div></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
