<?php

namespace App\Enums;

enum RouterStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Unknown = 'unknown';

    public function isReachable(): bool
    {
        return $this === self::Online;
    }
}
