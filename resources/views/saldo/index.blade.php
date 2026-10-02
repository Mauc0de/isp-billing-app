@extends('layouts.app')

@section('title', 'Saldo Pelanggan')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Saldo Pelanggan</h2>
        <p class="text-gray-500 text-xs md:text-sm">Top-up saldo dan pelunasan tagihan otomatis (auto-renew)</p>
    </div>
</header>

<div class="p-4 md:p-8 space-y-6">

    @if(session('status'))
        <div class="bg-emerald-50 text-emerald-700 text-sm px-4 py-3 rounded-xl border border-emerald-200">{{ session('status') }}</div>
    @endif
    @if($errors->any())
        <div class="bg-red-50 text-red-600 text-sm px-4 py-3 rounded-xl border border-red-200 space-y-1">
            @foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach
        </div>
    @endif

    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Total Pelanggan</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($ringkasan['total_pelanggan'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Total Saldo Tersimpan</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">Rp {{ number_format($ringkasan['total_saldo'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Auto-renew Aktif</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($ringkasan['auto_renew'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Daftar saldo per pelanggan --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Saldo per Pelanggan</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Pelanggan</th>
                        <th class="text-left p-4 text-gray-500">Paket</th>
                        <th class="text-left p-4 text-gray-500">Saldo</th>
                        <th class="text-left p-4 text-gray-500">Auto-renew</th>
                        <th class="text-right p-4 text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pelanggans as $pelanggan)
                    <tr class="border-t">
                        <td class="p-4">
                            <div class="font-medium">{{ $pelanggan->nama }}</div>
                            <div class="text-xs text-gray-400">{{ $pelanggan->telepon ?? '—' }}</div>
                        </td>
                        <td class="p-4 text-gray-500">{{ $pelanggan->paket->nama_paket ?? '—' }}</td>
                        <td class="p-4 font-semibold {{ $pelanggan->saldo > 0 ? 'text-emerald-600' : 'text-gray-400' }}">
                            Rp {{ number_format($pelanggan->saldo, 0, ',', '.') }}
                        </td>
                        <td class="p-4">
                            @if($pelanggan->auto_renew)
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-700">Aktif</span>
                            @else
                                <span class="px-3 py-1 rounded-full text-xs font-semibold bg-gray-200 text-gray-600">Nonaktif</span>
                            @endif
                        </td>
                        <td class="p-4 text-right">
                            <a href="{{ route('saldo.show', $pelanggan) }}" class="text-xs font-semibold text-blue-600 hover:underline">Kelola</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">Belum ada pelanggan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mutasi terbaru --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Mutasi Saldo Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Waktu</th>
                        <th class="text-left p-4 text-gray-500">Pelanggan</th>
                        <th class="text-left p-4 text-gray-500">Jenis</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Sumber</th>
                        <th class="text-left p-4 text-gray-500">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations as $mutation)
                    <tr class="border-t">
                        <td class="p-4 text-gray-500">{{ $mutation->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-4">{{ $mutation->pelanggan->nama ?? '—' }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $mutation->jenis->value === 'kredit' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                                {{ $mutation->jenis->label() }}
                            </span>
                        </td>
                        <td class="p-4 font-semibold {{ $mutation->jenis->value === 'kredit' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $mutation->jenis->value === 'kredit' ? '+' : '−' }} Rp {{ number_format($mutation->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-gray-500">{{ $mutation->sumber }}</td>
                        <td class="p-4 text-gray-700">Rp {{ number_format($mutation->saldo_akhir, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada mutasi saldo</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $mutations->links() }}</div>
    </div>
</div>
@endsection
