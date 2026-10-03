<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Pengeluaran;
use App\Models\Tagihan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class BillingController extends Controller
{
    public function pelanggan(): View
    {
        return view('pelanggan.index', [
            'pelanggan' => Pelanggan::with('paket')->get(),
        ]);
    }

    public function paket(): View
    {
        return view('paket.index', [
            'paket' => Paket::all(),
        ]);
    }

    public function tagihan(): View
    {
        return view('tagihan.index', [
            'tagihan' => Tagihan::with('pelanggan')->latest()->get(),
        ]);
    }

    public function tagihanPdf(Tagihan $tagihan)
    {
        $tagihan->load('pelanggan.paket', 'pelanggan');

        return \Barryvdh\DomPDF\Facade\Pdf::loadView('tagihan.pdf', [
            'tagihan' => $tagihan,
        ])->download($tagihan->nomor_tagihan.'.pdf');
    }

    public function pembayaran(): View
    {
        return view('pembayaran.index', [
            'pembayaran' => Pembayaran::with(['pelanggan', 'tagihan'])->get(),
        ]);
    }

    public function laporan(): View
    {
        $bulan = (int) request('bulan', now()->month);
        $tahun = (int) request('tahun', now()->year);

        $totalPendapatan = Pembayaran::where('status', 'berhasil')
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->sum('jumlah');

        $totalPengeluaran = (int) Pengeluaran::whereMonth('tanggal', $bulan)
            ->whereYear('tanggal', $tahun)
            ->sum('jumlah');

        $profit = $totalPendapatan - $totalPengeluaran;

        $totalTagihan = Tagihan::whereMonth('tanggal_terbit', $bulan)
            ->whereYear('tanggal_terbit', $tahun)
            ->sum('jumlah');

        $tagihanLunas = Tagihan::where('status', 'lunas')
            ->whereMonth('tanggal_terbit', $bulan)
            ->whereYear('tanggal_terbit', $tahun)
            ->count();

        $tagihanBelumBayar = Tagihan::where('status', 'belum_bayar')
            ->whereMonth('tanggal_terbit', $bulan)
            ->whereYear('tanggal_terbit', $tahun)
            ->count();

        $metode = Pembayaran::where('status', 'berhasil')
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->selectRaw('metode_pembayaran, count(*) as total, sum(jumlah) as nominal')
            ->groupBy('metode_pembayaran')
            ->get();

        // Trend 6 bulan untuk chart
        $labels = [];
        $data = [];
        $dataPengeluaran = [];
        for ($i = 5; $i >= 0; $i--) {
            $d = now()->setDate($tahun, $bulan, 1)->subMonths($i);
            $labels[] = $d->translatedFormat('M Y');
            $data[] = (int) Pembayaran::where('status', 'berhasil')
                ->whereMonth('tanggal_bayar', $d->month)
                ->whereYear('tanggal_bayar', $d->year)
                ->sum('jumlah');
            $dataPengeluaran[] = (int) Pengeluaran::whereMonth('tanggal', $d->month)
                ->whereYear('tanggal', $d->year)
                ->sum('jumlah');
        }

        return view('laporan.index', compact(
            'bulan', 'tahun', 'totalPendapatan', 'totalPengeluaran', 'profit',
            'totalTagihan', 'tagihanLunas', 'tagihanBelumBayar', 'metode', 'labels', 'data', 'dataPengeluaran',
        ));
    }

    public function export(Request $request)
    {
        $bulan = (int) $request->query('bulan', now()->month);
        $tahun = (int) $request->query('tahun', now()->year);
        $type = $request->query('type', 'csv');

        $pembayaran = Pembayaran::with(['pelanggan', 'tagihan'])
            ->where('status', 'berhasil')
            ->whereMonth('tanggal_bayar', $bulan)
            ->whereYear('tanggal_bayar', $tahun)
            ->get();

        if ($type === 'pdf') {
            return \Barryvdh\DomPDF\Facade\Pdf::loadView('laporan.pdf', compact('pembayaran', 'bulan', 'tahun'))
                ->download("laporan-{$tahun}-{$bulan}.pdf");
        }

        $filename = "laporan-{$tahun}-{$bulan}.csv";

        return response()->streamDownload(function () use ($pembayaran) {
            $handle = fopen('php://output', 'w');
            fputcsv($handle, ['Tanggal', 'Pelanggan', 'No Tagihan', 'Metode', 'Jumlah', 'Keterangan']);

            foreach ($pembayaran as $p) {
                fputcsv($handle, [
                    $p->tanggal_bayar?->format('Y-m-d'),
                    $p->pelanggan?->nama,
                    $p->tagihan?->nomor_tagihan,
                    $p->metode_pembayaran,
                    $p->jumlah,
                    $p->keterangan,
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv']);
    }

    public function pengeluaranIndex(): View
    {
        return view('pengeluaran.index', [
            'pengeluarans' => Pengeluaran::query()->latest('tanggal')->paginate(30),
        ]);
    }

    public function pengeluaranStore(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'tanggal' => ['required', 'date'],
            'jumlah' => ['required', 'integer', 'min:1'],
            'kategori' => ['required', 'string', 'max:50'],
            'keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        Pengeluaran::query()->create($validated);

        return back()->with('success', 'Pengeluaran dicatat.');
    }

    public function pengeluaranDestroy(Pengeluaran $pengeluaran): RedirectResponse
    {
        $pengeluaran->delete();

        return back()->with('success', 'Pengeluaran dihapus.');
    }
}
