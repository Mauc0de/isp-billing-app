<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPelanggan = Pelanggan::count();
        $totalPaket = Paket::count();
        $tagihanBelumBayar = Tagihan::where('status', 'belum_bayar')->count();
        $pembayaranBulanIni = Pembayaran::whereMonth('tanggal_bayar', now()->month)
            ->whereYear('tanggal_bayar', now()->year)
            ->sum('jumlah');

        // Pendapatan 6 bulan terakhir untuk chart
        $bulanLabels = [];
        $bulanData = [];
        for ($i = 5; $i >= 0; $i--) {
            $bulan = now()->subMonths($i);
            $bulanLabels[] = $bulan->translatedFormat('M Y');
            $bulanData[] = (int) Pembayaran::whereMonth('tanggal_bayar', $bulan->month)
                ->whereYear('tanggal_bayar', $bulan->year)
                ->sum('jumlah');
        }

        // Status tagihan untuk doughnut chart
        $statusTagihan = Tagihan::select('status', DB::raw('count(*) as total'))
            ->groupBy('status')
            ->pluck('total', 'status');

        $statusTagihanLabels = $statusTagihan->keys()
            ->map(fn ($s) => ucfirst(str_replace('_', ' ', $s)))
            ->values();

        return view('dashboard.index', compact(
            'totalPelanggan',
            'totalPaket',
            'tagihanBelumBayar',
            'pembayaranBulanIni',
            'bulanLabels',
            'bulanData',
            'statusTagihan',
            'statusTagihanLabels',
        ));
    }
}
