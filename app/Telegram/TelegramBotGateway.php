<?php

namespace App\Telegram;

use App\Contracts\TelegramGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;

/**
 * Gateway Telegram Bot API.
 *
 * POST {base_url}/bot{token}/sendMessage
 *   Body   : chat_id, text, parse_mode  (form-encoded)
 *   Sukses : {"ok":true,"result":{"message_id":123,...}}
 *   Gagal  : {"ok":false,"description":"chat not found"}
 *
 * Dokumentasi: https://core.telegram.org/bots/api#sendmessage
 */
final class TelegramBotGateway implements TelegramGateway
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly ?string $defaultChatId = null,
    ) {}

    public function send(string $message, ?string $chatId = null): TelegramSendResult
    {
        $target = $chatId ?? $this->defaultChatId;

        if ($target === null || $target === '') {
            return TelegramSendResult::skipped('Chat ID Telegram belum diisi.');
        }

        try {
            $response = Http::asForm()
                ->timeout((int) config('telegram.timeout'))
                ->post($this->endpoint(), [
                    'chat_id' => $target,
                    'text' => $message,
                    'parse_mode' => (string) config('telegram.parse_mode', 'HTML'),
                    'disable_web_page_preview' => 'true',
                ]);
        } catch (ConnectionException $exception) {
            return TelegramSendResult::failed(
                'Tidak bisa menghubungi server Telegram: '.$exception->getMessage(),
            );
        }

        $body = json_decode($response->body(), true);
        $body = is_array($body) ? $body : [];

        if (! ($body['ok'] ?? false)) {
            $reason = trim((string) ($body['description'] ?? ''));

            return TelegramSendResult::failed(
                $reason !== ''
                    ? $reason
                    : "Telegram merespons dengan HTTP {$response->status()}.",
            );
        }

        $messageId = $body['result']['message_id'] ?? null;

        return TelegramSendResult::sent($messageId === null ? null : (string) $messageId);
    }

    private function endpoint(): string
    {
        return rtrim($this->baseUrl, '/').'/bot'.$this->token.'/sendMessage';
    }
}
