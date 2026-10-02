<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cetak Voucher - {{ $batch->nama }}</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .voucher-card { break-inside: avoid; page-break-inside: avoid; }
        }
    </style>
</head>
<body class="bg-slate-100 text-slate-800 antialiased">
    <div class="no-print sticky top-0 bg-white border-b border-slate-200 px-4 md:px-8 py-4 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="font-bold text-lg text-slate-900">{{ $batch->nama }}</h1>
            <p class="text-xs text-slate-500">
                {{ $batch->vouchers->count() }} voucher
                @if($batch->paket) • {{ $batch->paket->nama_paket }} @endif
                • masa aktif {{ $batch->masa_aktif_hari }} hari
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('vouchers.index') }}" class="text-sm font-semibold text-slate-600 border border-slate-200 rounded-lg px-4 py-2 hover:bg-slate-50">Kembali</a>
            <button onclick="window.print()" class="text-sm font-semibold text-white bg-gradient-to-r from-[#00D2B4] via-[#0066FF] to-[#0052FF] rounded-lg px-4 py-2 shadow-md">Cetak</button>
        </div>
    </div>

    <div class="p-4 md:p-8">
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 max-w-6xl mx-auto">
            @foreach($batch->vouchers as $voucher)
            <div class="voucher-card bg-white rounded-xl border border-slate-200 shadow-sm p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-[10px] font-bold tracking-widest text-slate-400 uppercase">SATAK</span>
                    <span class="text-[10px] font-semibold text-slate-400">Hotspot</span>
                </div>
                <p class="font-mono font-extrabold text-lg text-slate-900 tracking-wider break-all text-center">{{ $voucher->kode }}</p>
                <div class="mt-3 pt-3 border-t border-dashed border-slate-200 text-[11px] text-slate-500 space-y-0.5">
                    @if($batch->paket)
                        <p>Paket: <span class="font-semibold text-slate-700">{{ $batch->paket->nama_paket }}</span></p>
                    @endif
                    <p>Harga: <span class="font-semibold text-slate-700">Rp {{ number_format($voucher->harga, 0, ',', '.') }}</span></p>
                    <p>Masa aktif: <span class="font-semibold text-slate-700">{{ $voucher->masa_aktif_hari }} hari</span></p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</body>
</html>
