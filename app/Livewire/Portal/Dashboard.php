<?php

namespace App\Livewire\Portal;

use App\Models\Pembayaran;
use App\Models\Tagihan;
use Livewire\Component;

class Dashboard extends Component
{
    public $pelanggan;

    public $paket;

    public int $tagihanBelumBayar = 0;

    public int $totalTagihanBelumBayar = 0;

    public int $totalPembayaran = 0;

    public $transaksiTerbaru = [];

    public function mount(): void
    {
        // Ambil pelanggan dari user yang sedang login.
        $this->pelanggan = auth()->user()?->pelanggan ?? null;

        if ($this->pelanggan === null) {
            return;
        }

        $this->paket = $this->pelanggan->paket;

        $belumBayar = Tagihan::where('pelanggan_id', $this->pelanggan->id)
            ->where('status', 'belum_bayar');

        $this->tagihanBelumBayar = (clone $belumBayar)->count();
        $this->totalTagihanBelumBayar = (int) (clone $belumBayar)->sum('jumlah');

        $this->totalPembayaran = (int) Pembayaran::where('pelanggan_id', $this->pelanggan->id)
            ->where('status', 'berhasil')
            ->sum('jumlah');

        $this->transaksiTerbaru = Pembayaran::with('tagihan')
            ->where('pelanggan_id', $this->pelanggan->id)
            ->latest()
            ->take(5)
            ->get();
    }

    public function render()
    {
        return view('livewire.portal.dashboard');
    }
}
