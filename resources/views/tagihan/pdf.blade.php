<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: DejaVu Sans, sans-serif; color: #1e293b; font-size: 12px; }
        .header { border-bottom: 3px solid #0052ff; padding-bottom: 12px; margin-bottom: 20px; }
        .brand { font-size: 22px; font-weight: bold; color: #0052ff; }
        .title { text-align: right; }
        h2 { margin: 0; color: #0052ff; }
        table { width: 100%; border-collapse: collapse; margin-top: 16px; }
        th, td { border: 1px solid #e2e8f0; padding: 8px 10px; text-align: left; }
        th { background: #f1f5f9; }
        .total { font-size: 16px; font-weight: bold; text-align: right; margin-top: 16px; }
        .footer { margin-top: 40px; font-size: 10px; color: #94a3b8; text-align: center; border-top: 1px solid #e2e8f0; padding-top: 8px; }
    </style>
</head>
<body>
    <div class="header">
        <table style="border: none; margin: 0;">
            <tr>
                <td style="border: none; padding: 0;"><span class="brand">SATAK</span><br><small>Konek Terus</small></td>
                <td style="border: none; padding: 0;" class="title">
                    <h2>INVOICE</h2>
                    <small>{{ $tagihan->nomor_tagihan }}</small>
                </td>
            </tr>
        </table>
    </div>

    <table style="border: none;">
        <tr>
            <td style="border: none; padding: 0;">
                <strong>Ditagihkan kepada:</strong><br>
                {{ $tagihan->pelanggan->nama }}<br>
                {{ $tagihan->pelanggan->alamat }}<br>
                {{ $tagihan->pelanggan->telepon }}
            </td>
            <td style="border: none; padding: 0; text-align: right;">
                <strong>Tanggal Terbit:</strong> {{ $tagihan->tanggal_terbit?->format('d/m/Y') }}<br>
                <strong>Jatuh Tempo:</strong> {{ $tagihan->jatuh_tempo?->format('d/m/Y') }}<br>
                <strong>Status:</strong> {{ strtoupper(str_replace('_', ' ', $tagihan->status)) }}
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr><th>Deskripsi</th><th style="text-align: right;">Jumlah</th></tr>
        </thead>
        <tbody>
            <tr>
                <td>{{ $tagihan->keterangan ?? ('Langganan '.($tagihan->pelanggan->paket->nama_paket ?? 'Internet')) }}</td>
                <td style="text-align: right;">Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</td>
            </tr>
        </tbody>
    </table>

    <p class="total">Total: Rp {{ number_format($tagihan->jumlah, 0, ',', '.') }}</p>

    <div class="footer">
        Invoice ini dibuat otomatis oleh sistem SATAK. Terima kasih.
    </div>
</body>
</html>
