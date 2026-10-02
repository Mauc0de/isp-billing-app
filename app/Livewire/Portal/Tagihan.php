<?php

namespace App\Livewire\Portal;

use App\Models\Tagihan as TagihanModel;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts::portal')]
class Tagihan extends Component
{
    use WithPagination;

    public $pelanggan;

    public function mount()
    {
        $this->pelanggan = auth()->user()?->pelanggan ?? null;
    }

    public function render()
    {
        $tagihans = TagihanModel::with('pelanggan')
            ->where('pelanggan_id', $this->pelanggan?->id)
            ->latest()
            ->paginate(10);

        return view('livewire.portal.tagihan', compact('tagihans'));
    }
}
