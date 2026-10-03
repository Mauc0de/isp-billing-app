<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
        h2 { color: #0052ff; margin-bottom: 4px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #e2e8f0; padding: 6px 8px; text-align: left; }
        th { background: #f1f5f9; }
        .total { margin-top: 12px; font-weight: bold; text-align: right; }
    </style>
</head>
<body>
    <h2>Laporan Pembayaran</h2>
    <p>Periode: {{ DateTime::createFromFormat('!m', $bulan)->format('F') }} {{ $tahun }}</p>

    <table>
        <thead>
            <tr><th>Tanggal</th><th>Pelanggan</th><th>No Tagihan</th><th>Metode</th><th style="text-align:right">Jumlah</th></tr>
        </thead>
        <tbody>
            @forelse($pembayaran as $p)
            <tr>
                <td>{{ $p->tanggal_bayar?->format('d/m/Y') }}</td>
                <td>{{ $p->pelanggan?->nama }}</td>
                <td>{{ $p->tagihan?->nomor_tagihan }}</td>
                <td>{{ $p->metode_pembayaran }}</td>
                <td style="text-align:right">Rp {{ number_format($p->jumlah, 0, ',', '.') }}</td>
            </tr>
            @empty
            <tr><td colspan="5" style="text-align:center">Tidak ada data</td></tr>
            @endforelse
        </tbody>
    </table>

    <p class="total">Total pemasukan: Rp {{ number_format($pembayaran->sum('jumlah'), 0, ',', '.') }}</p>
</body>
</html>
