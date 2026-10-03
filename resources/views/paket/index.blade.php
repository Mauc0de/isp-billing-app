@extends('layouts.app')

@section('title', 'Paket Internet')

@section('content')
<x-page-header title="Paket Internet" subtitle="Kelola paket layanan ISP" />

<div class="p-4 md:p-8">
    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 xl:grid-cols-3 md:gap-6">
        @forelse($paket as $p)
        <div class="rounded-2xl border border-slate-200/70 bg-white p-5 shadow-sm transition hover:shadow-md md:p-6">
            <div class="mb-3 flex items-start justify-between gap-3">
                <h3 class="text-base font-bold text-slate-900 md:text-lg">{{ $p->nama_paket }}</h3>
                <x-badge :tone="$p->status === 'aktif' ? 'emerald' : 'slate'">{{ $p->status }}</x-badge>
            </div>
            <p class="mb-2 text-sm font-medium text-brand-600">{{ $p->kecepatan ?? '—' }}</p>
            <p class="mb-4 text-xs text-slate-500">{{ $p->deskripsi ?? '—' }}</p>
            <p class="text-xl font-bold text-slate-900">Rp {{ number_format($p->harga, 0, ',', '.') }}<span class="text-sm font-normal text-slate-400">/{{ match($p->billing_cycle?->value) { 'quarterly' => '3 bulan', 'yearly' => 'tahun', default => 'bulan' } }}</span></p>
        </div>
        @empty
        <div class="col-span-full rounded-2xl border border-slate-200/70 bg-white p-8 text-center text-slate-400 shadow-sm">
            Belum ada paket
        </div>
        @endforelse
    </div>
</div>
@endsection
