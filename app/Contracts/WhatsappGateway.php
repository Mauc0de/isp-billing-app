<?php

namespace App\Contracts;

use App\Whatsapp\WhatsappSendResult;

/**
 * Gateway pengiriman WhatsApp (Fonnte / Wablas).
 */
interface WhatsappGateway
{
    /**
     * Kirim satu pesan ke satu nomor tujuan.
     *
     * Implementasi wajib mengembalikan hasil, bukan melempar exception, supaya
     * NotificationFailed tetap bisa dicatat di wa_notifications.
     */
    public function send(string $to, string $message): WhatsappSendResult;
}
