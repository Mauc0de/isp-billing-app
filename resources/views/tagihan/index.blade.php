@extends('layouts.app')

@section('title', 'Tagihan')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Tagihan</h2>
        <p class="text-gray-500 text-xs md:text-sm">Kelola tagihan pelanggan</p>
    </div>
</header>
<div class="p-4 md:p-8">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">No. Tagihan</th>
                        <th class="text-left p-4 text-gray-500">Pelanggan</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Tanggal Terbit</th>
                        <th class="text-left p-4 text-gray-500">Jatuh Tempo</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihan as $t)
                    <tr class="border-t">
                        <td class="p-4 font-medium">{{ $t->nomor_tagihan }}</td>
                        <td class="p-4 text-gray-500">{{ $t->pelanggan->nama ?? '-' }}</td>
                        <td class="p-4 whitespace-nowrap">Rp {{ number_format($t->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4 text-gray-500">{{ $t->tanggal_terbit?->format('d/m/Y') ?? '-' }}</td>
                        <td class="p-4 text-gray-500">{{ $t->jatuh_tempo?->format('d/m/Y') ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs
                                {{ $t->status === 'lunas' ? 'bg-green-100 text-green-700' : ($t->status === 'belum_bayar' ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $t->status }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Belum ada tagihan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
