<?php

namespace App\Enums;

/**
 * Hasil percobaan suspend/reaktivasi di router.
 */
enum SuspendLogStatus: string
{
    case Succeeded = 'succeeded';
    case Failed = 'failed';
    case Skipped = 'skipped';
}
