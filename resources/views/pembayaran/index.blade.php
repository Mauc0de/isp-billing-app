@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<x-page-header title="Pembayaran" subtitle="Riwayat pembayaran pelanggan" />

<div class="p-4 md:p-8">
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="p-4 font-bold">Pelanggan</th>
                        <th class="p-4 font-bold">Jumlah</th>
                        <th class="p-4 font-bold">Tanggal Bayar</th>
                        <th class="p-4 font-bold">Metode</th>
                        <th class="p-4 font-bold">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $p)
                    @php
                        $tone = match($p->status) {
                            'berhasil' => 'emerald',
                            'menunggu' => 'amber',
                            default => 'rose',
                        };
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                        <td class="p-4 font-medium text-slate-900">{{ $p->pelanggan->nama ?? '—' }}</td>
                        <td class="whitespace-nowrap p-4 font-semibold text-slate-900">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4 text-slate-500">{{ $p->tanggal_bayar?->format('d/m/Y') ?? '—' }}</td>
                        <td class="p-4 text-slate-500">{{ $p->metode_pembayaran ?? '—' }}</td>
                        <td class="p-4"><x-badge :tone="$tone">{{ $p->status }}</x-badge></td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada pembayaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
