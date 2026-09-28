<?php

namespace App\Whatsapp;

use App\Contracts\WhatsappGateway;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

/**
 * Gateway Fonnte.
 *
 * POST {base_url}/send
 *   Header : Authorization: {token}          (tanpa prefix "Bearer")
 *   Body   : target, message, countryCode      (form-encoded)
 *   Sukses : {"status":true,"id":["80367170"],"detail":"success! message in queue"}
 *   Gagal  : {"status":false,"reason":"token invalid"}
 *
 * Fonnte tidak konsisten mengapitalisasi key pada respons ("Status" pada
 * contoh error), jadi semua pembacaan key dilakukan case-insensitive.
 */
final class FonnteGateway implements WhatsappGateway
{
    public function __construct(
        private readonly string $token,
        private readonly string $baseUrl,
        private readonly string $countryCode = '0',
        private readonly bool $connectOnly = false,
    ) {}

    public function send(string $to, string $message): WhatsappSendResult
    {
        try {
            $response = Http::asForm()
                ->withHeaders(['Authorization' => $this->token])
                ->timeout((int) config('whatsapp.timeout'))
                ->post(rtrim($this->baseUrl, '/').'/send', [
                    'target' => $to,
                    'message' => $message,
                    // Nomor tujuan sudah dinormalisasi ke 62xxx oleh sistem,
                    // jadi filter awalan "0" milik Fonnte dimatikan.
                    'countryCode' => $this->countryCode,
                    'connectOnly' => $this->connectOnly ? 'true' : 'false',
                ]);
        } catch (ConnectionException $exception) {
            return WhatsappSendResult::failed(
                'Tidak bisa menghubungi server Fonnte: '.$exception->getMessage(),
            );
        }

        $body = $this->body($response->body());

        if ($response->failed()) {
            return WhatsappSendResult::failed(
                $this->string($body, 'reason')
                    ?? $this->string($body, 'detail')
                    ?? "Fonnte merespons dengan HTTP {$response->status()}.",
            );
        }

        if (! $this->isSuccessful($body)) {
            return WhatsappSendResult::failed(
                $this->string($body, 'reason')
                    ?? $this->string($body, 'detail')
                    ?? 'Fonnte menolak permintaan tanpa keterangan.',
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
        $id = $this->value($body, 'id');

        if (is_array($id)) {
            $id = $id[0] ?? null;
        }

        if ($id !== null && $id !== '') {
            return (string) $id;
        }

        $requestId = $this->value($body, 'requestid');

        return $requestId === null ? null : (string) $requestId;
    }

    /**
     * Fonnte membalas id sebagai array, jadi body dinormalisasi lebih dulu.
     *
     * @return array<string, mixed>
     */
    private function body(string $raw): array
    {
        $decoded = json_decode($raw, true);

        return is_array($decoded) ? $decoded : [];
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
