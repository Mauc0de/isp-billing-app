<?php

namespace App\Enums;

/**
 * Status tagihan.
 *
 * Nilai diselaraskan dengan kolom tagihans.status (default 'belum_bayar') dan
 * dengan view yang memeriksa 'lunas' serta 'belum_bayar'.
 */
enum InvoiceStatus: string
{
    case BelumBayar = 'belum_bayar';
    case Sebagian = 'sebagian';
    case Lunas = 'lunas';
    case Terlambat = 'terlambat';
    case Dibatalkan = 'dibatalkan';

    public function isOutstanding(): bool
    {
        return in_array($this, [self::BelumBayar, self::Sebagian, self::Terlambat], true);
    }

    public function isPaid(): bool
    {
        return $this === self::Lunas;
    }
}
