<?php

namespace App\Enums;

/**
 * Status permintaan pembayaran/top-up yang diajukan pelanggan.
 */
enum PaymentRequestStatus: string
{
    case Menunggu = 'menunggu';
    case Terverifikasi = 'terverifikasi';
    case Ditolak = 'ditolak';

    public function label(): string
    {
        return match ($this) {
            self::Menunggu => 'Menunggu verifikasi',
            self::Terverifikasi => 'Terverifikasi',
            self::Ditolak => 'Ditolak',
        };
    }

    public function isPending(): bool
    {
        return $this === self::Menunggu;
    }
}
