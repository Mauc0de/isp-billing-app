@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
<x-page-header title="Pelanggan" subtitle="Kelola data pelanggan ISP" />

<div class="p-4 md:p-8">
    <x-card>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <tr>
                        <th class="p-4 font-bold">Nama</th>
                        <th class="p-4 font-bold">Telepon</th>
                        <th class="p-4 font-bold">Email</th>
                        <th class="p-4 font-bold">Paket</th>
                        <th class="p-4 font-bold">Status</th>
                        <th class="p-4 font-bold">Tanggal Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan as $p)
                    @php
                        $tone = match(true) {
                            $p->status === \App\Enums\CustomerStatus::Aktif => 'emerald',
                            in_array($p->status, [\App\Enums\CustomerStatus::Menunggak, \App\Enums\CustomerStatus::Ditangguhkan], true) => 'rose',
                            default => 'slate',
                        };
                    @endphp
                    <tr class="border-t border-slate-100 hover:bg-slate-50/60">
                        <td class="p-4 font-medium text-slate-900">{{ $p->nama }}</td>
                        <td class="p-4 text-slate-500">{{ $p->telepon ?? '—' }}</td>
                        <td class="p-4 text-slate-500">{{ $p->email ?? '—' }}</td>
                        <td class="p-4 text-slate-500">{{ $p->paket->nama_paket ?? '—' }}</td>
                        <td class="p-4"><x-badge :tone="$tone">{{ $p->status->value }}</x-badge></td>
                        <td class="p-4 text-slate-500">{{ $p->tanggal_aktif?->format('d/m/Y') ?? '—' }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-slate-400">Belum ada pelanggan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-card>
</div>
@endsection
