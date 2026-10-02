@extends('layouts.app')

@section('title', 'Pembayaran Masuk')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Pembayaran Masuk</h2>
        <p class="text-gray-500 text-xs md:text-sm">Verifikasi bukti transfer manual dari pelanggan</p>
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
            <p class="text-xs font-medium text-slate-400">Menunggu</p>
            <p class="text-2xl font-extrabold text-amber-600 mt-1">{{ number_format($ringkasan['menunggu'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Terverifikasi</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($ringkasan['terverifikasi'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Ditolak</p>
            <p class="text-2xl font-extrabold text-rose-600 mt-1">{{ number_format($ringkasan['ditolak'], 0, ',', '.') }}</p>
        </div>
    </div>

    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-bold text-slate-900">Daftar Permintaan</h3>
            <form method="GET" class="flex items-center gap-2">
                <select name="status" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="menunggu" @selected($status === 'menunggu')>Menunggu</option>
                    <option value="terverifikasi" @selected($status === 'terverifikasi')>Terverifikasi</option>
                    <option value="ditolak" @selected($status === 'ditolak')>Ditolak</option>
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Pelanggan</th>
                        <th class="text-left p-4 text-gray-500">Tujuan</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Bukti</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                        <th class="text-right p-4 text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $req)
                    <tr class="border-t align-top">
                        <td class="p-4">
                            <div class="font-medium">{{ $req->pelanggan->nama ?? '—' }}</div>
                            <div class="text-xs text-gray-400">{{ $req->created_at->format('d/m/Y H:i') }}</div>
                        </td>
                        <td class="p-4 text-gray-500">
                            {{ $req->tujuan->label() }}
                            @if($req->tagihan)
                                <div class="text-xs text-gray-400">{{ $req->tagihan->nomor_tagihan }}</div>
                            @endif
                        </td>
                        <td class="p-4 font-semibold text-slate-900">Rp {{ number_format($req->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4">
                            @if($req->bukti_path)
                                <a href="{{ route('payment-requests.proof', $req) }}" target="_blank" class="text-xs font-semibold text-blue-600 hover:underline">Lihat bukti</a>
                            @else
                                <span class="text-xs text-gray-400">—</span>
                            @endif
                        </td>
                        <td class="p-4">
                            @php($color = match($req->status->value) {
                                'menunggu' => 'bg-amber-100 text-amber-700',
                                'terverifikasi' => 'bg-emerald-100 text-emerald-700',
                                default => 'bg-rose-100 text-rose-600',
                            })
                            <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $color }}">{{ $req->status->label() }}</span>
                        </td>
                        <td class="p-4">
                            @can('payment_requests.verify')
                                @if($req->status->isPending())
                                <div class="flex flex-col items-end gap-2">
                                    <form method="POST" action="{{ route('payment-requests.approve', $req) }}" class="flex items-center gap-2">
                                        @csrf
                                        <input type="text" name="catatan_admin" placeholder="Catatan (opsional)" maxlength="255"
                                               class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 outline-none w-40">
                                        <button type="submit" class="text-xs font-semibold px-3 py-1.5 rounded-lg border border-emerald-200 text-emerald-600 hover:bg-emerald-50">Setujui</button>
                                    </form>
                                    <form method="POST" action="{{ route('payment-requests.reject', $req) }}" onsubmit="return confirm('Tolak pembayaran ini?')">
                                        @csrf
                                        <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Tolak</button>
                                    </form>
                                </div>
                                @else
                                    <p class="text-xs text-gray-400 text-right">{{ $req->catatan_admin ?? '—' }}</p>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada permintaan pembayaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $requests->links() }}</div>
    </div>
</div>
@endsection
