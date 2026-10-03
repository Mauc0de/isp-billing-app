<?php

namespace App\Telegram;

use App\Contracts\TelegramGateway;
use App\Settings\TenantSettings;

/**
 * Memilih gateway Telegram sesuai konfigurasi tenant.
 *
 * Bot token dan chat ID admin dibaca dari config() (.env) karena tabel
 * settings berupa JSON plaintext dan tidak aman untuk kredensial. Tenant bisa
 * menyalakan/mematikan lewat setting telegram.enabled saja.
 */
class TelegramGatewayFactory
{
    public function __construct(private readonly TenantSettings $settings) {}

    public function make(): TelegramGateway
    {
        if (! $this->settings->telegramEnabled()) {
            return new NullTelegramGateway;
        }

        $token = config('telegram.bot_token');

        if (! is_string($token) || $token === '') {
            return new NullTelegramGateway;
        }

        return new TelegramBotGateway(
            token: $token,
            baseUrl: (string) config('telegram.base_url'),
            defaultChatId: $this->settings->telegramChatId() ?? (string) config('telegram.chat_id'),
        );
    }
}
