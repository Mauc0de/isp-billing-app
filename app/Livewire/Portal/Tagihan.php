<?php

namespace App\Livewire\Portal;

use App\Models\Invoice;
use Livewire\Component;
use Livewire\WithPagination;

class Tagihan extends Component
{
    use WithPagination;

    public $customer;

    public function mount()
    {
        $this->customer = auth()->user()->customer ?? null;
    }

    public function render()
    {
        $tagihans = Invoice::with('customer')
            ->where('customer_id', $this->customer?->id)
            ->latest()
            ->paginate(10);

        return view('livewire.portal.tagihan', compact('tagihans'));
    }
}
