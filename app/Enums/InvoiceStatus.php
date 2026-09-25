<?php

namespace App\Enums;

enum InvoiceStatus: string
{
    case Unpaid = 'unpaid';
    case Partial = 'partial';
    case Paid = 'paid';
    case Overdue = 'overdue';
    case Cancelled = 'cancelled';

    public function isOutstanding(): bool
    {
        return in_array($this, [self::Unpaid, self::Partial, self::Overdue], true);
    }
}
