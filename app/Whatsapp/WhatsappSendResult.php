<?php

namespace App\Whatsapp;

/**
 * Hasil satu percobaan kirim pesan WhatsApp.
 *
 * `skipped` dibedakan dari `failed` supaya provider yang sengaja dinonaktifkan
 * tidak-reported sebagai kegagalan dan memicu retry tanpa akhir.
 */
final readonly class WhatsappSendResult
{
    private function __construct(
        public bool $success,
        public ?string $messageId,
        public ?string $error,
        public bool $skipped,
    ) {}

    public static function sent(?string $messageId = null): self
    {
        return new self(success: true, messageId: $messageId, error: null, skipped: false);
    }

    public static function failed(string $error): self
    {
        return new self(success: false, messageId: null, error: $error, skipped: false);
    }

    public static function skipped(string $reason): self
    {
        return new self(success: false, messageId: null, error: $reason, skipped: true);
    }
}
