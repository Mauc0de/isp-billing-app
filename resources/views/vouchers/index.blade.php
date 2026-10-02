@extends('layouts.app')

@section('title', 'Voucher Hotspot')

@section('content')
<header class="bg-white shadow-sm py-4 md:py-5 px-4 md:px-8 flex flex-col sm:flex-row sm:justify-between sm:items-center gap-3">
    <div>
        <h2 class="text-xl md:text-2xl font-bold text-gray-800">Voucher Hotspot</h2>
        <p class="text-gray-500 text-xs md:text-sm">Buat dan cetak kupon prepaid</p>
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

    {{-- Ringkasan --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Total Voucher</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($ringkasan['total'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Belum Dipakai</p>
            <p class="text-2xl font-extrabold text-emerald-600 mt-1">{{ number_format($ringkasan['belum_dipakai'], 0, ',', '.') }}</p>
        </div>
        <div class="bg-white rounded-2xl shadow-sm p-5">
            <p class="text-xs font-medium text-slate-400">Terpakai</p>
            <p class="text-2xl font-extrabold text-slate-900 mt-1">{{ number_format($ringkasan['terpakai'], 0, ',', '.') }}</p>
        </div>
    </div>

    {{-- Form Generate --}}
    @can('vouchers.create')
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Generate Voucher Baru</h3>
            <p class="text-xs text-slate-400 mt-0.5">Kode dibuat otomatis tanpa karakter ambigu (0/O, 1/I/L).</p>
        </div>
        <form method="POST" action="{{ route('vouchers.store') }}" class="p-6 grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-4">
            @csrf
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Nama Batch</label>
                <input type="text" name="nama" value="{{ old('nama', 'Voucher '.now()->format('d/m/Y')) }}" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Paket</label>
                <select name="paket_id" class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 bg-white outline-none focus:border-blue-500">
                    <option value="">— Tanpa paket —</option>
                    @foreach($pakets as $paket)
                        <option value="{{ $paket->id }}" @selected(old('paket_id') === $paket->id)>{{ $paket->nama_paket }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Jumlah (1-1000)</label>
                <input type="number" name="jumlah" value="{{ old('jumlah', 10) }}" min="1" max="1000" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Masa Aktif (hari)</label>
                <input type="number" name="masa_aktif_hari" value="{{ old('masa_aktif_hari', 30) }}" min="1" max="365" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Prefix Kode (opsional)</label>
                <input type="text" name="kode_prefix" value="{{ old('kode_prefix') }}" placeholder="mis. WIFI-" maxlength="20"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div>
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Panjang Kode (6-24)</label>
                <input type="number" name="panjang_kode" value="{{ old('panjang_kode', 8) }}" min="6" max="24" required
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="md:col-span-2">
                <label class="block text-xs font-semibold text-slate-600 mb-1.5">Catatan (opsional)</label>
                <input type="text" name="catatan" value="{{ old('catatan') }}" maxlength="255"
                       class="w-full text-sm border border-slate-200 rounded-lg px-3 py-2.5 outline-none focus:border-blue-500 focus:ring-2 focus:ring-blue-100">
            </div>
            <div class="xl:col-span-4">
                <button type="submit" class="bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] text-white text-sm font-semibold px-5 py-2.5 rounded-xl shadow-md">
                    Generate Voucher
                </button>
            </div>
        </form>
    </div>
    @endcan

    {{-- Riwayat Batch --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100">
            <h3 class="font-bold text-slate-900">Batch Terbaru</h3>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[640px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Nama</th>
                        <th class="text-left p-4 text-gray-500">Paket</th>
                        <th class="text-left p-4 text-gray-500">Jumlah</th>
                        <th class="text-left p-4 text-gray-500">Dibuat</th>
                        <th class="text-right p-4 text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($batches as $batch)
                    <tr class="border-t">
                        <td class="p-4 font-medium">{{ $batch->nama }}</td>
                        <td class="p-4 text-gray-500">{{ $batch->paket->nama_paket ?? '—' }}</td>
                        <td class="p-4 text-gray-500">{{ $batch->vouchers_count }}</td>
                        <td class="p-4 text-gray-500">{{ $batch->created_at->format('d/m/Y H:i') }}</td>
                        <td class="p-4 text-right">
                            <a href="{{ route('vouchers.batch', $batch) }}" class="text-xs font-semibold text-blue-600 hover:underline">Lihat &amp; Cetak</a>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-gray-400">Belum ada batch voucher</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Daftar Voucher --}}
    <div class="bg-white rounded-2xl shadow-sm overflow-hidden">
        <div class="px-6 py-4 border-b border-slate-100 flex flex-wrap items-center justify-between gap-3">
            <h3 class="font-bold text-slate-900">Daftar Voucher</h3>
            <form method="GET" class="flex items-center gap-2">
                <select name="status" class="text-xs border border-slate-200 rounded-lg px-2 py-1.5 bg-white" onchange="this.form.submit()">
                    <option value="">Semua status</option>
                    <option value="belum_dipakai" @selected($status === 'belum_dipakai')>Belum dipakai</option>
                    <option value="terpakai" @selected($status === 'terpakai')>Terpakai</option>
                    <option value="kadaluarsa" @selected($status === 'kadaluarsa')>Kadaluarsa</option>
                </select>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[760px] text-sm">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="text-left p-4 text-gray-500">Kode</th>
                        <th class="text-left p-4 text-gray-500">Paket</th>
                        <th class="text-left p-4 text-gray-500">Harga</th>
                        <th class="text-left p-4 text-gray-500">Masa Aktif</th>
                        <th class="text-left p-4 text-gray-500">Status</th>
                        <th class="text-right p-4 text-gray-500">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($vouchers as $voucher)
                    <tr class="border-t">
                        <td class="p-4 font-mono font-semibold text-slate-900">{{ $voucher->kode }}</td>
                        <td class="p-4 text-gray-500">{{ $voucher->paket->nama_paket ?? '—' }}</td>
                        <td class="p-4 text-gray-500">Rp {{ number_format($voucher->harga, 0, ',', '.') }}</td>
                        <td class="p-4 text-gray-500">{{ $voucher->masa_aktif_hari }} hari</td>
                        <td class="p-4">
                            @php($label = $voucher->status->label())
                            <span class="px-3 py-1 rounded-full text-xs font-semibold
                                {{ $voucher->status->value === 'belum_dipakai' ? 'bg-emerald-100 text-emerald-700' : ($voucher->status->value === 'terpakai' ? 'bg-slate-200 text-slate-600' : 'bg-rose-100 text-rose-600') }}">
                                {{ $label }}
                            </span>
                        </td>
                        <td class="p-4 text-right">
                            @can('vouchers.delete')
                                @if($voucher->status->isUsable())
                                <form method="POST" action="{{ route('vouchers.destroy', $voucher) }}" onsubmit="return confirm('Hapus voucher ini?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button>
                                </form>
                                @endif
                            @endcan
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="6" class="p-8 text-center text-gray-400">Belum ada voucher</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4 border-t border-slate-100">{{ $vouchers->links() }}</div>
    </div>
</div>
@endsection
