<?php

namespace App\Enums;

/**
 * Status pembayaran.
 *
 * Kolom pembayarans.status memakai default 'berhasil'. Nilai 'lunas' yang
 * pernah diperiksa view pembayaran adalah istilah tagihan, bukan pembayaran,
 * sehingga tidak pernah muncul dari kolom ini.
 */
enum PaymentStatus: string
{
    case Menunggu = 'menunggu';
    case Berhasil = 'berhasil';
    case Gagal = 'gagal';
    case Dibatalkan = 'dibatalkan';

    public function isSuccessful(): bool
    {
        return $this === self::Berhasil;
    }
}
