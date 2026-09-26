<?php

namespace App\Livewire\Customers;

use Livewire\Component;

class Index extends Component
{
    public array $customers = [
        [
            'name'=> 'Andi',
            'nohp'=> '08123456789',
            'alamat'=> 'Jl. Raya No. 1',
            'paket'=> '10 Mbps',
            'status'=> 'Aktif',
                    ],
        [
            'name'=> 'Budi',
            'nohp'=> '08123456780',
            'alamat'=> 'Jl. Raya No. 2',
            'paket'=> '20 Mbps',
            'status'=> 'Nonaktif',
                    ],

        [
            'name'=> 'Dewi',
            'nohp'=> '08123456782',
            'alamat'=> 'Jl. Raya No. 4',
            'paket'=> '40 Mbps',
            'status'=> 'Suspend',
                    ],
    ];
    public function render()
    {
        return view('livewire.customers.index');
    }
}