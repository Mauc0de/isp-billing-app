<?php

namespace App\Enums;

/**
 * Tujuan pembayaran yang diajukan pelanggan.
 */
enum PaymentRequestPurpose: string
{
    case TopUpSaldo = 'topup_saldo';
    case BayarTagihan = 'bayar_tagihan';

    public function label(): string
    {
        return match ($this) {
            self::TopUpSaldo => 'Top-up saldo',
            self::BayarTagihan => 'Bayar tagihan',
        };
    }
}
