@extends('layouts.app')

@section('title', 'Saldo ' . $pelanggan->nama)

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Saldo: {{ $pelanggan->nama }}</h2>
        <p class="text-gray-500 text-xs md:text-sm">
            {{ $pelanggan->paket->nama_paket ?? 'Tanpa paket' }} • {{ $pelanggan->telepon ?? 'tanpa telepon' }}
        </p>
    </div>
    <a href="{{ route('saldo.index') }}" class="text-sm font-semibold text-slate-600 border border-slate-200 rounded-lg px-4 py-2 hover:bg-slate-50">Kembali</a>
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

    {{-- Kartu saldo + aksi --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-5">
        <div class="bg-gradient-to-br from-[#0052FF] to-[#00D2B4] rounded-2xl shadow-sm p-6 text-white">
            <p class="text-xs font-medium text-white/70">Saldo Saat Ini</p>
            <p class="text-3xl font-extrabold mt-2">Rp {{ number_format($pelanggan->saldo, 0, ',', '.') }}</p>
            <p class="text-xs text-white/80 mt-3">
                Auto-renew:
                <span class="font-semibold">{{ $pelanggan->auto_renew ? 'Aktif' : 'Nonaktif' }}</span>
            </p>
        </div>

        @can('saldo.manage')
        <div class="bg-white rounded-2xl shadow-sm p-6">
            <h3 class="font-bold text-slate-900 mb-3">Top-up Saldo</h3>
            <form method="POST" action="{{ route('saldo.topup', $pelanggan) }}" class="space-y-3">
                @csrf
                <input type="number" name="jumlah" min="1000" step="1000" value="50000" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <input type="text" name="keterangan" placeholder="Keterangan (opsional)" maxlength="255"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
                <button type="submit" class="w-full bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white text-sm font-semibold py-2.5 rounded-xl shadow-md">
                    Tambah Saldo
                </button>
            </form>
        </div>

        <div class="bg-white rounded-2xl shadow-sm p-6 flex flex-col justify-between">
            <div>
                <h3 class="font-bold text-slate-900 mb-1">Auto-renew</h3>
                <p class="text-xs text-slate-500">Bila aktif, tagihan yang jatuh tempo otomatis dilunasi dari saldo saat pemindaian harian.</p>
            </div>
            <form method="POST" action="{{ route('saldo.auto-renew', $pelanggan) }}" class="mt-4">
                @csrf @method('PATCH')
                <button type="submit" class="w-full text-sm font-semibold py-2.5 rounded-xl border {{ $pelanggan->auto_renew ? 'border-rose-200 text-rose-600 hover:bg-rose-50' : 'border-emerald-200 text-emerald-600 hover:bg-emerald-50' }}">
                    {{ $pelanggan->auto_renew ? 'Nonaktifkan Auto-renew' : 'Aktifkan Auto-renew' }}
                </button>
            </form>
        </div>
        @endcan
    </div>

    {{-- Tagihan --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Tagihan</h3>
            <p class="text-xs text-slate-400 mt-0.5">Tombol "Bayar dari saldo" hanya aktif bila saldo mencukupi.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Nomor</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Jatuh Tempo</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                        <th class="text-right p-4 text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($tagihans as $tagihan)
                    @php($outstanding = in_array($tagihan->status, ['belum_bayar', 'sebagian', 'terlambat'], true))
                    <tr class="border-t">
                        <td class="p-4 font-mono">{{ $tagihan->nomor_tagihan }}</td>
                        <td class="p-4">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4 text-gray-500">{{ $tagihan->jatuh_tempo?->format('d/m/Y') }}</td>
                        <td class="p-4">
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $tagihan->status === 'lunas' ? 'bg-emerald-100 text-emerald-700' : ($tagihan->status === 'belum_bayar' ? 'bg-rose-100 text-rose-600' : 'bg-amber-100 text-amber-700') }}">
                                {{ str_replace('_', ' ', $tagihan->status) }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @can('saldo.manage')
                                @if($outstanding)
                                <form method="POST" action="{{ route('saldo.bayar', $tagihan) }}">
                                    @csrf
                                    <button type="submit"
                                            @disabled($pelanggan->saldo < $tagihan->jumlah)
                                            class="text-xs font-semibold px-3 py-1.5 rounded-lg border {{ $pelanggan->saldo >= $tagihan->jumlah ? 'border-blue-200 text-blue-600 hover:bg-blue-50' : 'border-slate-200 text-slate-300 cursor-not-allowed' }}">
                                        Bayar dari saldo
                                    </button>
                                </form>
                                @else
                                    <span class="text-xs text-gray-400">—</span>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">Belum ada tagihan</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Mutasi --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Riwayat Mutasi Saldo</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Waktu</th>
                        <th class="text-left p-4 text-gray-500">Jenis</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Sumber</th>
                        <th class="text-left p-4 text-gray-500">Keterangan</th>
                        <th class="text-left p-4 text-gray-500">Saldo Akhir</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mutations as $mutation)
                    <tr class="border-t">
                        <td class="p-4 text-gray-500">{{ $mutation->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-4">
                            <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $mutation->jenis->value === 'kredit' ? 'bg-emerald-100 text-emerald-700' : 'bg-rose-100 text-rose-600' }}">
                                {{ $mutation->jenis->label() }}
                            </span>
                        </td>
                        <td class="p-4 font-semibold {{ $mutation->jenis->value === 'kredit' ? 'text-emerald-600' : 'text-rose-600' }}">
                            {{ $mutation->jenis->value === 'kredit' ? '+' : '−' }} Rp {{ number_format($mutation->jumlah, 0, ',', '.') }}
                        </td>
                        <td class="p-4 text-gray-500">{{ $mutation->sumber }}</td>
                        <td class="p-4 text-gray-500">{{ $mutation->keterangan ?? '—' }}</td>
                        <td class="p-4 text-gray-700">Rp {{ number_format($mutation->saldo_akhir, 0, ',', '.') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada mutasi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $mutations->links() }}</div>
    </div>
</div>
@endsection
