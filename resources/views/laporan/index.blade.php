@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Laporan</h2>
        <p class="text-gray-500 text-xs md:text-sm">Laporan keuangan ISP</p>
    </div>
</header>
<div class="p-4 md:p-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 md:gap-6 mb-6 md:mb-8">
        <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
            <p class="text-gray-500 text-sm">Total Pendapatan</p>
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-2 break-words">Rp {{ number_format($totalPendapatan, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
            <p class="text-gray-500 text-sm">Total Tagihan</p>
            <h3 class="text-xl md:text-2xl font-bold text-gray-800 mt-2 break-words">Rp {{ number_format($totalTagihan, 0, ',', '.') }}</h3>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
            <p class="text-gray-500 text-sm">Tagihan Lunas</p>
            <h3 class="text-2xl md:text-3xl font-bold text-green-600 mt-2">{{ $tagihanLunas }}</h3>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
            <p class="text-gray-500 text-sm">Tagihan Belum Bayar</p>
            <h3 class="text-2xl md:text-3xl font-bold text-red-600 mt-2">{{ $tagihanBelumBayar }}</h3>
        </div>
    </div>
</div>
@endsection
