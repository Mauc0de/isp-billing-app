<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'belum_bayar';
    case Partial = 'sebagian';
    case Paid = 'lunas';
    case Overdue = 'terlambat';
    case Cancelled = 'dibatalkan';

    public function isOutstanding(): bool
    {
        return in_array($this, [self::Unpaid, self::Partial, self::Overdue], true);
    }
}
