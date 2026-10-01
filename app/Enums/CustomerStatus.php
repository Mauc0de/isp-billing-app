<?php

namespace App\Enums;

enum CustomerStatus: string
{
    case Active = 'aktif';
    case Suspended = 'suspended';
    case Terminated = 'terminated';
}
