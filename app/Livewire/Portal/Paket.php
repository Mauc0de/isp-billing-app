<?php

namespace App\Livewire\Portal;

use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts::portal')]
class Paket extends Component
{
    public $pelanggan;

    public $paket;

    public function mount()
    {
        $this->pelanggan = auth()->user()?->pelanggan ?? null;
        $this->paket = $this->pelanggan?->paket;
    }

    public function render()
    {
        return view('livewire.portal.paket');
    }
}
