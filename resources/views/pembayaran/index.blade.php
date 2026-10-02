@extends('layouts.app')

@section('title', 'Pembayaran')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Pembayaran</h2>
        <p class="text-gray-500 text-xs md:text-sm">Riwayat pembayaran pelanggan</p>
    </div>
</header>
<div class="p-4 md:p-8">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Pelanggan</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Tanggal Bayar</th>
                        <th class="text-left p-4 text-gray-500">Metode</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pembayaran as $p)
                    <tr class="border-t">
                        <td class="p-4 font-medium">{{ $p->pelanggan->nama ?? '-' }}</td>
                        <td class="p-4 whitespace-nowrap">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4 text-gray-500">{{ $p->tanggal_bayar?->format('d/m/Y') ?? '-' }}</td>
                        <td class="p-4 text-gray-500">{{ $p->metode_pembayaran ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs
                                {{ $p->status === 'berhasil' ? 'bg-green-100 text-green-700' : ($p->status === 'menunggu' ? 'bg-yellow-100 text-yellow-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $p->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="p-8 text-center text-gray-400">Belum ada pembayaran</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
