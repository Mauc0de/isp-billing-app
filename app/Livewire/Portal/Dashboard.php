<?php

namespace App\Livewire\Portal;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Livewire\Component;

class Dashboard extends Component
{
    public $pelanggan;

    public $paket;

    public $tagihanBelumBayar;

    public $totalPembayaran;

    public $transaksiTerbaru;

    public function mount()
    {
        // Ambil pelanggan dari user yang sedang login.
        $this->pelanggan = auth()->user()?->pelanggan ?? null;

        if ($this->pelanggan) {
            $this->paket = $this->pelanggan->paket;
            $this->tagihanBelumBayar = Tagihan::where('pelanggan_id', $this->pelanggan->id)
                ->where('status', 'belum_bayar')
                ->count();
            $this->totalPembayaran = Pembayaran::where('pelanggan_id', $this->pelanggan->id)
                ->where('status', 'berhasil')
                ->sum('jumlah');
            $this->transaksiTerbaru = Pembayaran::with('tagihan')
                ->where('pelanggan_id', $this->pelanggan->id)
                ->latest()
                ->take(5)
                ->get();
        }
    }

    public function render()
    {
        return view('livewire.portal.dashboard');
    }
}
