<?php

namespace App\Livewire\Portal;

use App\Models\Payment;
use Livewire\Component;
use Livewire\WithPagination;

class Pembayaran extends Component
{
    use WithPagination;

    public $customer;

    public function mount()
    {
        $this->customer = auth()->user()->customer ?? null;
    }

    public function render()
    {
        $pembayarans = Payment::with('invoice')
            ->where('customer_id', $this->customer?->id)
            ->latest()
            ->paginate(10);

        return view('livewire.portal.pembayaran', compact('pembayarans'));
    }
}
