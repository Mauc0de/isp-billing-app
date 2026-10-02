<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $totalPelanggan = Pelanggan::count();
        $totalPaket = Paket::count();
        $tagihanBelumBayar = Tagihan::where('status', 'belum_bayar')->count();
        $pembayaranBulanIni = Pembayaran::whereMonth('tanggal_bayar', now()->month)->sum('jumlah');

        return view('dashboard.index', compact(
            'totalPelanggan',
            'totalPaket',
            'tagihanBelumBayar',
            'pembayaranBulanIni',
        ));
    }
}
