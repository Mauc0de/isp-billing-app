<?php

namespace App\Enums;

/**
 * Jenis mutasi saldo pelanggan.
 */
enum SaldoMutationType: string
{
    case Kredit = 'kredit';
    case Debit = 'debit';

    public function label(): string
    {
        return match ($this) {
            self::Kredit => 'Kredit',
            self::Debit => 'Debit',
        };
    }
}
