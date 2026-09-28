@extends('layouts.app')

@section('title', 'Paket Internet')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Paket Internet</h2>
        <p class="text-gray-500 text-xs md:text-sm">Kelola paket layanan ISP</p>
    </div>
</header>
<div class="p-4 md:p-8">
    <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-3 gap-4 md:gap-6">
        @forelse($paket as $p)
        <div class="bg-white rounded-2xl shadow-sm p-5 md:p-6">
            <div class="flex justify-between items-start mb-3">
                <h3 class="font-bold text-base md:text-lg">{{ $p->nama_paket }}</h3>
                <span class="px-3 py-1 rounded-full text-xs
                    {{ $p->status === 'aktif' ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-700' }}">
                    {{ $p->status }}
                </span>
            </div>
            <p class="text-gray-500 text-sm mb-2">{{ $p->kecepatan }}</p>
            <p class="text-gray-500 text-xs mb-4">{{ $p->deskripsi }}</p>
            <p class="text-xl font-bold text-blue-700">Rp {{ number_format($p->harga, 0, ',', '.') }}<span class="text-sm font-normal text-gray-400">/bulan</span></p>
        </div>
        @empty
        <div class="col-span-full bg-white rounded-2xl shadow-sm p-8 text-center text-gray-400">
            Belum ada paket
        </div>
        @endforelse
    </div>
</div>
@endsection
