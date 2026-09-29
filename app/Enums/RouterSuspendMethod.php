<?php

namespace App\Enums;

/**
 * Cara memotong akses pelanggan di RouterOS.
 *
 * PppSecret menonaktifkan entri PPP secret (PPPoE/Hotspot). AddressList
 * menambahkan IP Address ke address-list blokir tanpa mengubah kredensial.
 */
enum RouterSuspendMethod: string
{
    case PppSecret = 'ppp_secret';
    case AddressList = 'address_list';

    public function label(): string
    {
        return match ($this) {
            self::PppSecret => 'Disable PPP secret',
            self::AddressList => 'Blokir di address-list',
        };
    }
}
