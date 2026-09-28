<?php

namespace App\Whatsapp;

use App\Contracts\WhatsappGateway;
use Illuminate\Support\Facades\Log;

/**
 * Dipakai saat provider WhatsApp dinonaktifkan atau token belum diisi.
 *
 * Pesan tetap dicatat di wa_notifications dengan status "skipped" supaya riwayat
 * tetap utuh dan alur otomatis bisa diuji sebelum kredensial kantor masuk.
 */
final class NullGateway implements WhatsappGateway
{
    public function send(string $to, string $message): WhatsappSendResult
    {
        Log::debug('Notifikasi WhatsApp dilewati karena provider belum dikonfigurasi.', [
            'to' => $to,
        ]);

        return WhatsappSendResult::skipped(
            'Provider WhatsApp belum dikonfigurasi, pesan tidak dikirim.',
        );
    }
}
