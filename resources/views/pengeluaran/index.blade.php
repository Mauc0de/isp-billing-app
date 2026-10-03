@extends('layouts.app')

@section('title', 'Pengeluaran')

@section('content')
<x-page-header title="Pengeluaran" subtitle="Catat biaya operasional ISP" />

<div class="p-4 md:p-8">
    @if(session('success'))
        <div class="mb-6 rounded-xl border border-emerald-200 bg-emerald-50 p-4 text-sm font-medium text-emerald-700">{{ session('success') }}</div>
    @endif

    <x-card class="mb-6">
        <x-slot:title>Tambah Pengeluaran</x-slot:title>
        <form method="POST" action="{{ route('pengeluaran.store') }}" class="grid grid-cols-1 gap-4 p-5 md:grid-cols-4">
            @csrf
            <div><label class="label">Tanggal</label><input type="date" name="tanggal" value="{{ now()->toDateString() }}" class="input" required></div>
            <div><label class="label">Jumlah (Rp)</label><input type="number" name="jumlah" min="1" class="input" required></div>
            <div><label class="label">Kategori</label>
                <select name="kategori" class="input">
                    <option value="operasional">Operasional</option>
                    <option value="bandwidth">Bandwidth</option>
                    <option value="perangkat">Perangkat</option>
                    <option value="gaji">Gaji</option>
                    <option value="sewa">Sewa</option>
                    <option value="lain-lain">Lain-lain</option>
                </select>
            </div>
            <div><label class="label">Keterangan</label><input name="keterangan" class="input"></div>
            <div class="md:col-span-4"><button class="btn-primary">Catat Pengeluaran</button></div>
        </form>
    </x-card>

    <x-card>
        <x-slot:title>Riwayat</x-slot:title>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-slate-50 text-left text-xs uppercase tracking-wider text-slate-400">
                    <tr><th class="p-4 font-bold">Tanggal</th><th class="p-4 font-bold">Kategori</th><th class="p-4 font-bold">Keterangan</th><th class="p-4 font-bold">Jumlah</th><th class="p-4"></th></tr>
                </thead>
                <tbody>
                    @forelse($pengeluarans as $p)
                    <tr class="border-t border-slate-100">
                        <td class="p-4 text-slate-500">{{ $p->tanggal->format('d/m/Y') }}</td>
                        <td class="p-4 capitalize">{{ $p->kategori }}</td>
                        <td class="p-4 text-slate-500">{{ $p->keterangan }}</td>
                        <td class="p-4 font-semibold">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
                        <td class="p-4">
                            <form method="POST" action="{{ route('pengeluaran.destroy', $p) }}" onsubmit="return confirm('Hapus catatan ini?')">@csrf @method('DELETE')<button class="text-xs font-semibold text-rose-600 hover:underline">Hapus</button></form>
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="p-8 text-center text-slate-400">Belum ada pengeluaran</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="p-4">{{ $pengeluarans->links() }}</div>
    </x-card>
</div>
@endsection
