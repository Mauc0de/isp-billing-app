@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<x-page-header title="Laporan" subtitle="Laporan keuangan ISP" />

<div class="p-4 md:p-8">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <x-stat-card label="Total Pendapatan" value="Rp {{ number_format($totalPendapatan, 0, ',', '.') }}" tone="emerald" />
        <x-stat-card label="Total Tagihan" value="Rp {{ number_format($totalTagihan, 0, ',', '.') }}" tone="brand" />
        <x-stat-card label="Tagihan Lunas" :value="$tagihanLunas" tone="emerald" />
        <x-stat-card label="Tagihan Belum Bayar" :value="$tagihanBelumBayar" tone="rose" />
    </div>
</div>
@endsection
