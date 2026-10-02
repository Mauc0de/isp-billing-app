@extends('layouts.app')

@section('title', 'Pelanggan')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Pelanggan</h2>
        <p class="text-gray-500 text-xs md:text-sm">Kelola data pelanggan ISP</p>
    </div>
</header>
<div class="p-4 md:p-8">
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Nama</th>
                        <th class="text-left p-4 text-gray-500">Telepon</th>
                        <th class="text-left p-4 text-gray-500">Email</th>
                        <th class="text-left p-4 text-gray-500">Paket</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                        <th class="text-left p-4 text-gray-500">Tanggal Aktif</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggan as $p)
                    <tr class="border-t">
                        <td class="p-4 font-medium">{{ $p->nama }}</td>
                        <td class="p-4 text-gray-500">{{ $p->telepon }}</td>
                        <td class="p-4 text-gray-500">{{ $p->email }}</td>
                        <td class="p-4 text-gray-500">{{ $p->paket->nama_paket ?? '-' }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs
                                {{ $p->status === \App\Enums\CustomerStatus::Aktif ? 'bg-green-100 text-green-700' : (in_array($p->status, [\App\Enums\CustomerStatus::Menunggak, \App\Enums\CustomerStatus::Ditangguhkan], true) ? 'bg-red-100 text-red-700' : 'bg-gray-100 text-gray-700') }}">
                                {{ $p->status->value }}
                            </span>
                        </td>
                        <td class="p-4 text-gray-500">{{ $p->tanggal_aktif?->format('d/m/Y') ?? '-' }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="p-8 text-center text-gray-400">Belum ada pelanggan</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
