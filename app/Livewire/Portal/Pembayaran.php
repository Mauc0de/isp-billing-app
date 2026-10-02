<?php

namespace App\Livewire\Portal;

use App\Models\Pembayaran as PembayaranModel;
use Livewire\Component;
use Livewire\WithPagination;

class Pembayaran extends Component
{
    use WithPagination;

    public $pelanggan;

    public function mount()
    {
        $this->pelanggan = auth()->user()?->pelanggan ?? null;
    }

    public function render()
    {
        $pembayarans = PembayaranModel::with('tagihan')
            ->where('pelanggan_id', $this->pelanggan?->id)
            ->latest()
            ->paginate(10);

        return view('livewire.portal.pembayaran', compact('pembayarans'));
    }
}
