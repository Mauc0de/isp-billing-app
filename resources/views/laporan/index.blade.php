@extends('layouts.app')

@section('title', 'Laporan')

@section('content')
<x-page-header title="Laporan" subtitle="Laporan keuangan ISP">
    <x-slot:actions>
        <a href="{{ route('laporan.export', ['bulan' => $bulan, 'tahun' => $tahun, 'type' => 'csv']) }}" class="btn-secondary">Export CSV</a>
        <a href="{{ route('laporan.export', ['bulan' => $bulan, 'tahun' => $tahun, 'type' => 'pdf']) }}" class="btn-primary">Export PDF</a>
    </x-slot:actions>
</x-page-header>

<div class="p-4 md:p-8">
    <x-card class="mb-6">
        <form method="GET" action="{{ route('laporan.index') }}" class="flex flex-wrap items-end gap-4 p-5">
            <div>
                <label class="label">Bulan</label>
                <select name="bulan" class="input">
                    @foreach(range(1, 12) as $b)
                        <option value="{{ $b }}" @selected($b === $bulan)>{{ DateTime::createFromFormat('!m', $b)->format('F') }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="label">Tahun</label>
                <select name="tahun" class="input">
                    @foreach(range(now()->year - 2, now()->year + 1) as $t)
                        <option value="{{ $t }}" @selected($t === $tahun)>{{ $t }}</option>
                    @endforeach
                </select>
            </div>
            <button class="btn-primary">Filter</button>
        </form>
    </x-card>

    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-4 md:gap-6">
        <x-stat-card label="Pendapatan Bulan Ini" value="Rp {{ number_format($totalPendapatan, 0, ',', '.') }}" tone="emerald" />
        <x-stat-card label="Pengeluaran Bulan Ini" value="Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}" tone="rose" />
        <x-stat-card label="Profit Bersih" value="Rp {{ number_format($profit, 0, ',', '.') }}" tone="{{ $profit >= 0 ? 'emerald' : 'rose' }}" />
        <x-stat-card label="Tagihan Belum Bayar" :value="$tagihanBelumBayar" tone="amber" />
    </div>

    <div class="mt-6 grid grid-cols-1 gap-4 xl:grid-cols-3 md:gap-6">
        <x-card class="xl:col-span-2">
            <x-slot:title>Tren Pendapatan</x-slot:title>
            <x-slot:subtitle>6 bulan terakhir</x-slot:subtitle>
            <div class="p-5"><canvas id="chartLaporan" height="120"></canvas></div>
        </x-card>

        <x-card title="Metode Pembayaran" subtitle="Breakdown bulan ini">
            <div class="p-5">
                <table class="w-full text-sm">
                    <thead class="text-left text-xs uppercase tracking-wider text-slate-400">
                        <tr><th class="pb-3">Metode</th><th class="pb-3">Jumlah</th><th class="pb-3 text-right">Nominal</th></tr>
                    </thead>
                    <tbody>
                        @forelse($metode as $m)
                        <tr class="border-t border-slate-100">
                            <td class="py-3 font-medium capitalize">{{ $m->metode_pembayaran }}</td>
                            <td class="py-3 text-slate-500">{{ $m->total }}x</td>
                            <td class="py-3 text-right font-semibold">Rp {{ number_format($m->nominal, 0, ',', '.') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="3" class="py-6 text-center text-slate-400">Belum ada pembayaran</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </x-card>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    if (typeof Chart === 'undefined') return;
    new Chart(document.getElementById('chartLaporan'), {
        type: 'line',
        data: {
            labels: @json($labels),
            datasets: [
                { label: 'Pendapatan', data: @json($data), borderColor: '#0052ff', backgroundColor: 'rgba(0,82,255,0.08)', fill: true, tension: 0.35 },
                { label: 'Pengeluaran', data: @json($dataPengeluaran), borderColor: '#ef4444', backgroundColor: 'rgba(239,68,68,0.05)', fill: true, tension: 0.35 },
            ]
        },
        options: { plugins: { legend: { display: true } }, scales: { y: { beginAtZero: true, ticks: { callback: v => 'Rp ' + v.toLocaleString('id-ID') } } } }
    });
});
</script>
@endsection
