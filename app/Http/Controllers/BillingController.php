<?php

namespace App\Http\Controllers;

use App\Models\Paket;
use App\Models\Pelanggan;
use App\Models\Pembayaran;
use App\Models\Tagihan;
use Illuminate\Contracts\View\View;

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
            'tagihan' => Tagihan::with('pelanggan')->get(),
        ]);
    }

    public function pembayaran(): View
    {
        return view('pembayaran.index', [
            'pembayaran' => Pembayaran::with(['pelanggan', 'tagihan'])->get(),
        ]);
    }

    public function laporan(): View
    {
        return view('laporan.index', [
            'totalPendapatan' => Pembayaran::where('status', 'berhasil')->sum('jumlah'),
            'totalTagihan' => Tagihan::sum('jumlah'),
            'tagihanLunas' => Tagihan::where('status', 'lunas')->count(),
            'tagihanBelumBayar' => Tagihan::where('status', 'belum_bayar')->count(),
        ]);
    }
}
