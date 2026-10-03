<?php

namespace App\Telegram;

use App\Enums\TelegramStatus;
use App\Models\PaymentRequest;
use App\Models\Pelanggan;
use App\Models\TelegramNotification;
use App\Models\Tagihan;
use App\Settings\TenantSettings;
use Illuminate\Support\Facades\Log;

/**
 * Menyusun dan mengirim notifikasi Telegram ke admin.
 *
 * Selalu mencatat baris telegram_notifications lebih dulu supaya kejadian
 * penting tetap terekam walau gateway belum dikonfigurasi (status skipped).
 * Pengiriman sengaja sinkron: ini alert ke admin, bukan pesan massal ke
 * pelanggan, jadi tidak perlu antrean retry.
 */
class TelegramNotifier
{
    public function __construct(
        private readonly TelegramGatewayFactory $factory,
        private readonly TenantSettings $settings,
    ) {}

    public function notifyCustomerCreated(Pelanggan $pelanggan): void
    {
        $this->send('customer_created', 'Pelanggan baru terdaftar.', [
            'Nama' => $pelanggan->nama,
            'Nomor' => $pelanggan->customer_number ?? '-',
            'Paket' => $pelanggan->paket?->nama_paket ?? '-',
        ], $pelanggan->tenant_id);
    }

    public function notifySuspended(Pelanggan $pelanggan, ?Tagihan $tagihan = null): void
    {
        $this->send('suspended', 'Pelanggan disuspend otomatis.', [
            'Nama' => $pelanggan->nama,
            'Tagihan' => $tagihan?->nomor_tagihan ?? '-',
            'Jumlah' => $tagihan === null ? '-' : 'Rp '.number_format((float) $tagihan->jumlah, 0, ',', '.'),
        ], $pelanggan->tenant_id);
    }

    public function notifyReactivated(Pelanggan $pelanggan): void
    {
        $this->send('reactivated', 'Pelanggan diaktifkan kembali.', [
            'Nama' => $pelanggan->nama,
        ], $pelanggan->tenant_id);
    }

    public function notifyPaymentIncoming(PaymentRequest $request): void
    {
        $this->send('payment_incoming', 'Pembayaran masuk menunggu verifikasi.', [
            'Pelanggan' => $request->pelanggan?->nama ?? '-',
            'Tujuan' => $request->tujuan->label(),
            'Jumlah' => 'Rp '.number_format($request->jumlah, 0, ',', '.'),
        ], $request->tenant_id);
    }

    public function notifyRouterOffline(string $routerName, string $error): void
    {
        $this->send('router_offline', 'Router tidak terjangkau.', [
            'Router' => $routerName,
            'Error' => $error,
        ], null);
    }

    /**
     * @param  array<string, string>  $fields
     */
    public function send(string $event, string $headline, array $fields = [], ?string $tenantId = null): void
    {
        // Tanpa konteks tenant, model tenant-scoped tidak bisa dibuat; alert
        // sistem (mis. router) tetap dicoba kirim lewat gateway.
        $message = $this->compose($event, $headline, $fields);

        $gateway = $this->factory->make();
        $result = $gateway->send($message);

        if ($tenantId === null) {
            // Tetap catat bila ada konteks; kalau tidak, cukup log.
            Log::info('Notifikasi Telegram (tanpa tenant).', ['event' => $event, 'sent' => $result->success]);

            return;
        }

        TelegramNotification::query()->create([
            'tenant_id' => $tenantId,
            'event' => $event,
            'chat_id' => $this->settings->telegramChatId(),
            'message' => $message,
            'status' => match (true) {
                $result->skipped => TelegramStatus::Skipped,
                $result->success => TelegramStatus::Sent,
                default => TelegramStatus::Failed,
            },
            'provider_message_id' => $result->messageId,
            'error' => $result->error,
            'sent_at' => $result->success ? now() : null,
        ]);
    }

    /**
     * @param  array<string, string>  $fields
     */
    private function compose(string $event, string $headline, array $fields): string
    {
        $emoji = (string) config('telegram.emoji.'.$event, '🔔');
        $lines = ["<b>{$emoji} {$headline}</b>"];

        foreach ($fields as $label => $value) {
            $lines[] = '<b>'.e($label).':</b> '.e((string) $value);
        }

        return implode("\n", $lines);
    }
}
