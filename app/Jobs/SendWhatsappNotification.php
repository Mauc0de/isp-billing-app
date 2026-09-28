<?php

namespace App\Jobs;

use App\Enums\WhatsappStatus;
use App\Models\WhatsappNotification;
use App\Settings\TenantSettings;
use App\Whatsapp\WhatsappGatewayFactory;

/**
 * Mengirim satu notifikasi WhatsApp yang sudah tercatat di queue.
 *
 * Job ini idempoten: notifikasi yang sudah punya status selain Queued dilewati,
 * sehingga job yang terduplikasi tidak mengirim pesan dua kali.
 */
class SendWhatsappNotification extends TenantJob
{
    public int $tries = 3;

    /**
     * @var list<int>
     */
    public array $backoff = [60, 300, 900];

    public function __construct(
        string $tenantId,
        public string $notificationId,
    ) {
        parent::__construct($tenantId);
    }

    protected function handleInTenant(): void
    {
        $gateways = $this->resolve(WhatsappGatewayFactory::class);
        $settings = $this->resolve(TenantSettings::class);

        $notification = WhatsappNotification::query()->find($this->notificationId);

        if ($notification === null || $notification->status !== WhatsappStatus::Queued) {
            return;
        }

        $result = $gateways->make()->send($notification->to_number, $notification->message);

        $notification->forceFill([
            'provider' => $settings->whatsappProvider(),
            'status' => match (true) {
                $result->skipped => WhatsappStatus::Skipped,
                $result->success => WhatsappStatus::Sent,
                default => WhatsappStatus::Failed,
            },
            'provider_message_id' => $result->messageId,
            'error' => $result->error,
            'attempts' => $notification->attempts + 1,
            'sent_at' => $result->success ? now() : null,
        ])->save();
    }
}
