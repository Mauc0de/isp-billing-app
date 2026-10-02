@extends('layouts.app')

@section('title', 'Dashboard')

@section('content')
<x-page-header title="Dashboard" subtitle="Ringkasan keuangan dan pelanggan ISP">
    <x-slot:actions>
        <div class="flex items-center gap-3">
            <div class="text-right">
                <p class="text-sm font-semibold text-slate-800">{{ auth()->user()->name }}</p>
                <p class="text-xs text-slate-500">Administrator</p>
            </div>
            <div class="flex h-10 w-10 items-center justify-center rounded-full bg-gradient-to-br from-accent-500 to-brand-600 font-bold text-white">
                {{ strtoupper(mb_substr(auth()->user()->name, 0, 1)) }}
            </div>
        </div>
    </x-slot:actions>
</x-page-header>

<div class="p-4 md:p-8">
    <div class="mb-6 rounded-2xl bg-gradient-to-r from-brand-700 via-brand-600 to-brand-500 p-5 text-white shadow-lg shadow-brand-500/20 md:mb-8 md:p-7">
        <h2 class="mb-1 text-lg font-bold md:mb-2 md:text-2xl">Selamat Datang di SATAK</h2>
        <p class="text-sm text-brand-100 md:text-base">Kelola pelanggan, pembayaran, tagihan dan keuangan ISP melalui satu dashboard.</p>
    </div>

    @php
        $aktif = \App\Models\Pelanggan::where('status', \App\Enums\CustomerStatus::Aktif->value)->count();
        $menunggak = \App\Models\Pelanggan::where('status', \App\Enums\CustomerStatus::Menunggak->value)->count();
        $nonaktif = \App\Models\Pelanggan::whereIn('status', [\App\Enums\CustomerStatus::Berhenti->value, \App\Enums\CustomerStatus::Ditangguhkan->value])->count();
        $basis = max(1, $totalPelanggan);
    @endphp

    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:mb-8 md:gap-6">
        <x-stat-card label="Total Pelanggan" :value="$totalPelanggan" tone="brand" />
        <x-stat-card label="Paket Internet" :value="$totalPaket" />
        <x-stat-card label="Tagihan Belum Dibayar" :value="$tagihanBelumBayar" tone="amber" />
        <x-stat-card label="Pendapatan Bulan Ini" value="Rp {{ number_format($pembayaranBulanIni, 0, ',', '.') }}" tone="emerald" />
    </div>

    <div class="grid grid-cols-1 gap-4 xl:grid-cols-3 md:gap-6">
        <x-card class="xl:col-span-2">
            <x-slot:title>Transaksi Terbaru</x-slot:title>
            <x-slot:subtitle>Pembayaran pelanggan terbaru</x-slot:subtitle>
            <x-slot:actions>
                <a href="{{ route('pembayaran.index') }}" class="text-xs font-semibold text-brand-600 hover:underline md:text-sm">Lihat Semua</a>
            </x-slot:actions>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[520px] text-sm">
                    <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="p-4 font-bold">Pelanggan</th>
                            <th class="p-4 font-bold">Jumlah</th>
                            <th class="p-4 font-bold">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse(\App\Models\Pembayaran::with('pelanggan')->latest()->take(5)->get() as $pembayaran)
                        <tr class="border-t border-slate-100">
                            <td class="p-4 font-medium">{{ $pembayaran->pelanggan->nama ?? '-' }}</td>
                            <td class="whitespace-nowrap p-4">Rp {{ number_format($pembayaran->jumlah, 0, ',', '.') }}</td>
                            <td class="p-4"><x-badge tone="emerald">{{ $pembayaran->status }}</x-badge></td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="p-8 text-center text-slate-400">Belum ada transaksi</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>

        <x-card title="Status Pelanggan" subtitle="Kondisi pelanggan saat ini">
            <div class="space-y-5 p-5 md:p-6">
                @foreach([
                    ['Aktif', $aktif, 'bg-emerald-500'],
                    ['Menunggak', $menunggak, 'bg-amber-500'],
                    ['Nonaktif', $nonaktif, 'bg-slate-400'],
                ] as [$label, $jumlah, $bar])
                    <div>
                        <div class="mb-2 flex justify-between"><span class="text-sm text-slate-600">{{ $label }}</span><span class="font-semibold">{{ $jumlah }}</span></div>
                        <div class="h-2 w-full overflow-hidden rounded-full bg-slate-100">
                            <div class="{{ $bar }} h-2 rounded-full transition-all duration-500" style="width: {{ round($jumlah / $basis * 100) }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </x-card>
    </div>
</div>
@endsection
