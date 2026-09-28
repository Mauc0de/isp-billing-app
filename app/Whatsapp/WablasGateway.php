<?php

namespace App\Whatsapp;

use App\Contracts\WhatsappGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Gateway Wablas (API V1).
 *
 * POST {base_url}/send-message
 *   Header : Authorization: {token}.{secret_key}
 *   Body   : phone, message, ref_id            (form-encoded)
 *   Sukses : {"status":true,"data":{"messages":[{"id":"5be4...","status":"pending"}]}}
 *
 * Bentuk `data` tidak seragam: satu perangkat membalasnya sebagai objek dengan
 * `messages`, banyak perangkat membalasnya sebagai array of object. Keduanya
 * ditangani di messageId().
 */
final class WablasGateway implements WhatsappGateway
{
    public function __construct(
        private readonly string $token,
        private readonly string $secretKey,
        private readonly string $baseUrl,
        private readonly bool $sendRefId = true,
    ) {}

    public function send(string $to, string $message): WhatsappSendResult
    {
        $payload = [
            'phone' => $to,
            'message' => $message,
        ];

        if ($this->sendRefId) {
            $payload['ref_id'] = (string) (int) (microtime(true) * 1000);
        }

        try {
            $response = Http::asForm()
                ->withHeaders([
                    'Authorization' => $this->token.'.'.$this->secretKey,
                    'url' => rtrim($this->baseUrl, '/'),
                ])
                ->timeout((int) config('whatsapp.timeout'))
                ->post(rtrim($this->baseUrl, '/').'/send-message', $payload);
        } catch (ConnectionException $exception) {
            return WhatsappSendResult::failed(
                'Tidak bisa menghubungi server Wablas: '.$exception->getMessage(),
            );
        }

        $decoded = json_decode($response->body(), true);
        $body = is_array($decoded) ? $decoded : [];

        if ($response->failed()) {
            return WhatsappSendResult::failed(
                $this->string($body, 'message') ?? "Wablas merespons dengan HTTP {$response->status()}.",
            );
        }

        if (! $this->isSuccessful($body)) {
            return WhatsappSendResult::failed(
                $this->string($body, 'message') ?? 'Wablas menolak permintaan tanpa keterangan.',
            );
        }

        return WhatsappSendResult::sent($this->messageId($body));
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function isSuccessful(array $body): bool
    {
        $status = $this->value($body, 'status');

        if ($status === null) {
            return false;
        }

        if (is_bool($status)) {
            return $status;
        }

        return in_array(Str::lower((string) $status), ['true', '1', 'success'], true);
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function messageId(array $body): ?string
    {
        $data = $this->value($body, 'data');

        if (! is_array($data)) {
            return null;
        }

        // Bentuk multi-perangkat: data adalah list of object, ambil yang pertama.
        if (array_is_list($data)) {
            $data = $data[0] ?? [];

            if (! is_array($data)) {
                return null;
            }
        }

        foreach (['messages', 'message'] as $key) {
            $messages = $data[$key] ?? null;

            if (is_array($messages) && array_is_list($messages)) {
                $messages = $messages[0] ?? null;
            }

            if (is_array($messages) && isset($messages['id'])) {
                return (string) $messages['id'];
            }
        }

        return isset($data['id']) ? (string) $data['id'] : null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function value(array $body, string $key): mixed
    {
        foreach ($body as $candidate => $value) {
            if (Str::lower((string) $candidate) === $key) {
                return $value;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $body
     */
    private function string(array $body, string $key): ?string
    {
        $value = $this->value($body, $key);

        if ($value === null || is_array($value)) {
            return null;
        }

        $value = trim((string) $value);

        return $value === '' ? null : $value;
    }
}
