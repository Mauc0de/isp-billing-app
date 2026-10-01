<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'berhasil';
    case Failed = 'gagal';
    case Reversed = 'dibalikkan';
}
