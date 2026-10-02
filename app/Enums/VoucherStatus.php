<?php

namespace App\Enums;

/**
 * Status voucher hotspot/prepaid.
 *
 * Nilainya disimpan sebagai string di kolom vouchers.status.
 */
enum VoucherStatus: string
{
    case BelumDipakai = 'belum_dipakai';
    case Terpakai = 'terpakai';
    case Kadaluarsa = 'kadaluarsa';
    case Dibatalkan = 'dibatalkan';

    public function label(): string
    {
        return match ($this) {
            self::BelumDipakai => 'Belum dipakai',
            self::Terpakai => 'Terpakai',
            self::Kadaluarsa => 'Kadaluarsa',
            self::Dibatalkan => 'Dibatalkan',
        };
    }

    public function isUsable(): bool
    {
        return $this === self::BelumDipakai;
    }
}
