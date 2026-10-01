<?php

namespace App\Livewire\Portal;

use App\Models\Customer;
use Livewire\Component;

class Paket extends Component
{
    public $customer;
    public $package;

    public function mount()
    {
        $this->customer = auth()->user()->customer ?? null;
        $this->package = $this->customer?->package;
    }

    public function render()
    {
        return view('livewire.portal.paket');
    }
}
