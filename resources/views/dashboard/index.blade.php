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

    <div class="mb-6 grid grid-cols-2 gap-4 md:grid-cols-4">
        @php
            $routerTotal = \App\Models\Router::whereNull('archived_at')->count();
            $routerOnline = \App\Models\Router::where('status', \App\Enums\RouterStatus::Online->value)->count();
            $routerOffline = \App\Models\Router::where('status', \App\Enums\RouterStatus::Offline->value)->count();
        @endphp
        <div class="rounded-2xl border border-slate-100 bg-white p-4 shadow-sm">
            <p class="text-xs font-semibold uppercase tracking-wider text-slate-400">Router</p>
            <p class="mt-1 text-2xl font-black text-slate-800">{{ $routerTotal }}</p>
        </div>
        <div class="rounded-2xl border border-emerald-100 bg-emerald-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-emerald-600">Online</p>
            <p class="mt-1 text-2xl font-black text-emerald-700">{{ $routerOnline }}</p>
        </div>
        <div class="rounded-2xl border border-rose-100 bg-rose-50 p-4">
            <p class="text-xs font-semibold uppercase tracking-wider text-rose-600">Offline</p>
            <p class="mt-1 text-2xl font-black text-rose-700">{{ $routerOffline }}</p>
        </div>
        <a href="{{ route('router.index') }}" class="flex items-center justify-center rounded-2xl border border-dashed border-slate-200 p-4 text-sm font-semibold text-brand-600 hover:bg-brand-50">Kelola Router →</a>
    </div>

    <div class="mb-6 grid grid-cols-1 gap-4 xl:grid-cols-3 md:mb-8 md:gap-6">
        <x-card class="xl:col-span-2">
            <x-slot:title>Pendapatan 6 Bulan Terakhir</x-slot:title>
            <x-slot:subtitle>Total pembayaran pelanggan per bulan</x-slot:subtitle>
            <div class="p-5">
                <canvas id="chartPendapatan" height="120"></canvas>
            </div>
        </x-card>

        <x-card title="Status Tagihan" subtitle="Komposisi tagihan saat ini">
            <div class="p-5">
                <canvas id="chartTagihan"></canvas>
            </div>
        </x-card>
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

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;

    new Chart(document.getElementById('chartPendapatan'), {
        type: 'bar',
        data: {
            labels: @json($bulanLabels),
            datasets: [{
                label: 'Pendapatan (Rp)',
                data: @json($bulanData),
                backgroundColor: 'rgba(0, 102, 255, 0.7)',
                borderRadius: 8,
            }]
        },
        options: {
            responsive: true,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } }
        }
    });

    new Chart(document.getElementById('chartTagihan'), {
        type: 'doughnut',
        data: {
            labels: @json($statusTagihanLabels),
            datasets: [{
                data: @json(array_values($statusTagihan->toArray())),
                backgroundColor: ['#f59e0b', '#10b981', '#ef4444', '#64748b', '#0052ff'],
            }]
        },
        options: { responsive: true, cutout: '65%' }
    });
});
</script>
@endsection
