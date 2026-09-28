<?php

namespace App\Whatsapp;

use App\Enums\WhatsappStatus;
use App\Jobs\SendWhatsappNotification;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\SuspendLog;
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

    public function notifySuspended(SuspendLog $log, Customer $customer, ?Invoice $invoice = null): void
    {
        $this->dispatch(
            customer: $customer,
            invoice: $invoice,
            suspendLog: $log,
            template: $this->settings->template('suspend'),
        );
    }

    public function notifyReactivated(SuspendLog $log, Customer $customer): void
    {
        $this->dispatch(
            customer: $customer,
            invoice: null,
            suspendLog: $log,
            template: $this->settings->template('reactivate'),
        );
    }

    public function notifyDueReminder(Customer $customer, Invoice $invoice): void
    {
        $this->dispatch(
            customer: $customer,
            invoice: $invoice,
            suspendLog: null,
            template: $this->settings->template('due_reminder'),
        );
    }

    private function dispatch(
        Customer $customer,
        ?Invoice $invoice,
        ?SuspendLog $suspendLog,
        string $template,
    ): void {
        $to = $customer->whatsappTarget();

        if ($to === null) {
            Log::info('Notifikasi WhatsApp dilewati karena pelanggan tidak punya nomor WhatsApp.', [
                'customer_id' => $customer->getKey(),
            ]);

            return;
        }

        $notification = WhatsappNotification::query()->create([
            'tenant_id' => $customer->tenant_id,
            'customer_id' => $customer->getKey(),
            'invoice_id' => $invoice?->getKey(),
            'suspend_log_id' => $suspendLog?->getKey(),
            'to_number' => $to,
            'message' => $this->render($template, $customer, $invoice),
            'status' => WhatsappStatus::Queued,
        ]);

        SendWhatsappNotification::dispatch((string) $customer->tenant_id, $notification->getKey());
    }

    private function render(string $template, Customer $customer, ?Invoice $invoice): string
    {
        $placeholders = [
            '{customer}' => (string) $customer->name,
            '{customer_number}' => (string) $customer->customer_number,
            '{tenant}' => (string) $customer->tenant?->name,
            '{package}' => (string) ($customer->package?->name ?? $invoice?->package_name ?? '-'),
            '{router}' => (string) ($customer->router?->name ?? '-'),
            '{invoice_number}' => (string) ($invoice?->invoice_number ?? '-'),
            '{amount}' => $invoice === null ? '-' : $this->rupiah((float) $invoice->total),
            '{due_date}' => $invoice?->due_date?->format('d/m/Y') ?? '-',
            '{overdue_days}' => $this->overdueDays($invoice),
            '{reason}' => (string) ($customer->status_reason ?? '-'),
        ];

        return strtr($template, $placeholders);
    }

    private function overdueDays(?Invoice $invoice): string
    {
        if ($invoice?->due_date === null) {
            return '-';
        }

        $days = $invoice->due_date->diffInDays(now()->startOfDay(), false);

        return (string) max(0, $days);
    }

    private function rupiah(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }
}
