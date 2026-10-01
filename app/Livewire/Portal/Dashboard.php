<?php

namespace App\Livewire\Portal;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\Customer;
use Livewire\Component;

class Dashboard extends Component
{
    public $customer;
    public $package;
    public $tagihanBelumBayar;
    public $totalPembayaran;
    public $transaksiTerbaru;

    public function mount()
    {
        // Ambil customer dari user yang sedang login
        $this->customer = auth()->user()->customer ?? null;

        if ($this->customer) {
            $this->package = $this->customer->package;
            $this->tagihanBelumBayar = Invoice::where('customer_id', $this->customer->id)
                ->where('status', 'belum_bayar')
                ->count();
            $this->totalPembayaran = Payment::where('customer_id', $this->customer->id)
                ->where('status', 'berhasil')
                ->sum('amount');
            $this->transaksiTerbaru = Payment::with('invoice')
                ->where('customer_id', $this->customer->id)
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
