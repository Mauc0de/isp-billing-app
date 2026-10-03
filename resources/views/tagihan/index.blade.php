@extends('layouts.app')

@section('title', 'Tagihan')

@section('content')
<x-page-header title="Tagihan" subtitle="Kelola tagihan pelanggan" />

<div class="p-4 md:p-8">
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="p-4 font-bold">No. Tagihan</th>
                        <th class="p-4 font-bold">Pelanggan</th>
                        <th class="p-4 font-bold">Jumlah</th>
                        <th class="p-4 font-bold">Tanggal Terbit</th>
                        <th class="p-4 font-bold">Jatuh Tempo</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihan as $t)
                    @php
                        $tone = match($t->status) {
                            'lunas' => 'emerald',
                            'belum_bayar' => 'rose',
                            'terlambat' => 'amber',
                            default => 'slate',
                        };
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                        <td class="p-4 font-mono font-medium text-slate-900">{{ $t->nomor_tagihan }}</td>
                        <td class="p-4 text-slate-500">{{ $t->pelanggan->nama ?? '—' }}</td>
                        <td class="whitespace-nowrap p-4 font-semibold text-slate-900">Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4 text-slate-500">{{ $t->tanggal_terbit?->format('d/m/Y') ?? '—' }}</td>
                        <td class="p-4 text-slate-500">{{ $t->jatuh_tempo?->format('d/m/Y') ?? '—' }}</td>
                        <td class="p-4"><x-badge :tone="$tone">{{ str_replace('_', ' ', $t->status) }}</x-badge></td>
                        <td class="p-4">
                            <a href="{{ route('tagihan.pdf', $t) }}" class="text-xs font-semibold text-brand-600 hover:underline">PDF</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-400">Belum ada tagihan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
