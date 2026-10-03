<?php

namespace App\Telegram;

use App\Contracts\TelegramGateway;
use Illuminate\Support\Facades\Log;

/**
 * Dipakai saat Telegram dinonaktifkan atau token belum diisi.
 *
 * Pesan tetap dicatat di telegram_notifications dengan status "skipped" supaya
 * riwayat tetap utuh dan alur otomatis bisa diuji sebelum token masuk.
 */
final class NullTelegramGateway implements TelegramGateway
{
    public function send(string $message, ?string $chatId = null): TelegramSendResult
    {
        Log::debug('Notifikasi Telegram dilewati karena belum dikonfigurasi.');

        return TelegramSendResult::skipped(
            'Telegram belum dikonfigurasi, pesan tidak dikirim.',
        );
    }
}
