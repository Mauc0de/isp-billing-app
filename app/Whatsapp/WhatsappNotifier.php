<?php

namespace App\Whatsapp;

use App\Enums\WhatsappStatus;
use App\Jobs\SendWhatsappNotification;
use App\Models\Pelanggan;
use App\Models\SuspendLog;
use App\Models\Tagihan;
use App\Models\WhatsappNotification;
use App\Settings\TenantSettings;
use Illuminate\Support\Facades\Log;

/**
 * Menyusun pesan notifikasi lalu meneruskannya ke queue.
 *
 * Baris wa_notifications selalu dibuat lebih dulu, lalu job dikirim memakai
 * ID-nya. Dengan begitu riwayat notifikasi tetap lengkap walau queue mati,
 * dan job tidak perlu membawa isi pesan (yang bisa besar) saat diserialisasi.
 */
class WhatsappNotifier
{
    public function __construct(private readonly TenantSettings $settings) {}

    public function notifySuspended(SuspendLog $log, Pelanggan $pelanggan, ?Tagihan $tagihan = null): void
    {
        $this->dispatch(
            pelanggan: $pelanggan,
            tagihan: $tagihan,
            suspendLog: $log,
            template: $this->settings->template('suspend'),
        );
    }

    public function notifyReactivated(SuspendLog $log, Pelanggan $pelanggan): void
    {
        $this->dispatch(
            pelanggan: $pelanggan,
            tagihan: null,
            suspendLog: $log,
            template: $this->settings->template('reactivate'),
        );
    }

    public function notifyDueReminder(Pelanggan $pelanggan, Tagihan $tagihan): void
    {
        $this->dispatch(
            pelanggan: $pelanggan,
            tagihan: $tagihan,
            suspendLog: null,
            template: $this->settings->template('due_reminder'),
        );
    }

    private function dispatch(
        Pelanggan $pelanggan,
        ?Tagihan $tagihan,
        ?SuspendLog $suspendLog,
        string $template,
    ): void {
        $to = $pelanggan->whatsappTarget();

        if ($to === null) {
            Log::info('Notifikasi WhatsApp dilewati karena pelanggan tidak punya nomor WhatsApp.', [
                'pelanggan_id' => $pelanggan->getKey(),
            ]);

            return;
        }

        $notification = WhatsappNotification::query()->create([
            'tenant_id' => $pelanggan->tenant_id,
            'pelanggan_id' => $pelanggan->getKey(),
            'tagihan_id' => $tagihan?->getKey(),
            'suspend_log_id' => $suspendLog?->getKey(),
            'to_number' => $to,
            'message' => $this->render($template, $pelanggan, $tagihan),
            'status' => WhatsappStatus::Queued,
        ]);

        SendWhatsappNotification::dispatch((string) $pelanggan->tenant_id, $notification->getKey());
    }

    private function render(string $template, Pelanggan $pelanggan, ?Tagihan $tagihan): string
    {
        $placeholders = [
            '{customer}' => (string) $pelanggan->nama,
            '{customer_number}' => (string) $pelanggan->customer_number,
            '{tenant}' => (string) $pelanggan->tenant?->name,
            '{package}' => (string) ($pelanggan->paket?->nama_paket ?? '-'),
            '{router}' => (string) ($pelanggan->router?->name ?? '-'),
            '{invoice_number}' => (string) ($tagihan?->nomor_tagihan ?? '-'),
            '{amount}' => $tagihan === null ? '-' : $this->rupiah((float) $tagihan->jumlah),
            '{due_date}' => $tagihan?->jatuh_tempo?->format('d/m/Y') ?? '-',
            '{overdue_days}' => $this->overdueDays($tagihan),
            '{reason}' => (string) ($pelanggan->status_reason ?? '-'),
        ];

        return strtr($template, $placeholders);
    }

    private function overdueDays(?Tagihan $tagihan): string
    {
        if ($tagihan?->jatuh_tempo === null) {
            return '-';
        }

        $days = $tagihan->jatuh_tempo->diffInDays(now()->startOfDay(), false);

        return (string) max(0, $days);
    }

    private function rupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
