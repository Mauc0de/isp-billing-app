<?php

namespace App\Exceptions;

use RuntimeException;

/**
 * Gateway WhatsApp tidak terkonfigurasi (provider disabled atau token kosong).
 * Kondisi ini diharapkan selama masa setup, bukan bug.
 */
class WhatsappNotConfigured extends RuntimeException
{
    public static function disabled(): self
    {
        return new self('Provider WhatsApp untuk tenant ini sedang dinonaktifkan.');
    }

    public static function missingToken(string $provider): self
    {
        return new self("Token untuk provider WhatsApp '{$provider}' belum diisi.");
    }
}
