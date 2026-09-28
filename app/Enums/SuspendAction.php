<?php

namespace App\Enums;

enum SuspendAction: string
{
    case Suspend = 'suspend';
    case Reactivate = 'reactivate';

    public function label(): string
    {
        return match ($this) {
            self::Suspend => 'Suspend',
            self::Reactivate => 'Reaktivasi',
        };
    }
}
