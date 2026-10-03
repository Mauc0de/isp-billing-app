<?php

namespace App\Contracts;

use App\Telegram\TelegramSendResult;

/**
 * Gateway notifikasi Telegram (Bot API).
 *
 * Implementasi wajib mengembalikan hasil, bukan melempar exception, supaya
 * kegagalan bisa dicatat di telegram_notifications tanpa menghentikan alur.
 */
interface TelegramGateway
{
    /**
     * Kirim satu pesan ke chat admin yang dikonfigurasi.
     */
    public function send(string $message, ?string $chatId = null): TelegramSendResult;
}
