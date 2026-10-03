<?php

namespace App\Enums;

/**
 * Status pengiriman notifikasi Telegram.
 */
enum TelegramStatus: string
{
    case Queued = 'queued';
    case Sent = 'sent';
    case Failed = 'failed';
    case Skipped = 'skipped';
}
