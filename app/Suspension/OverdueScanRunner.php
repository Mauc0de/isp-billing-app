<?php

namespace App\Suspension;

use App\Jobs\SuspendCustomer;
use App\Models\Customer;
use App\Models\Invoice;
use App\Tenancy\TenantRunner;
use App\Whatsapp\WhatsappNotifier;

/**
 * Orkestrasi satu putaran pemindaian tagihan untuk seluruh tenant aktif.
 *
 * Dipisah dari ScanOverdueInvoices supaya dua hal terpenuhi: job di queue
 * tetap tipis (cuma meneruskan), dan command isp:scan-overdue bisa menampilkan
 * jumlah hasil yang sebenarnya, bukan menebak dari flag.
 *
 * Kelas ini tidak pernah mengubah status pelanggan atau invoice — hanya
 * meneruskan ke CustomerSuspender lewat job.
 */
class OverdueScanRunner
{
    public function __construct(
        private readonly TenantRunner $tenants,
        private readonly OverdueInvoiceScanner $scanner,
        private readonly WhatsappNotifier $notifier,
    ) {}

    public function run(bool $suspend = true, bool $remind = true): ScanReport
    {
        $report = ScanReport::empty();

        $this->tenants->forEachActiveTenant(function () use (&$report, $suspend, $remind): void {
            $report = new ScanReport(
                tenants: $report->tenants + 1,
                suspended: $report->suspended + ($suspend ? $this->queueSuspensions() : 0),
                reminded: $report->reminded + ($remind ? $this->queueReminders() : 0),
            );
        });

        return $report;
    }

    /**
     * @return int jumlah pelanggan yang/jobnya diantrekan
     */
    private function queueSuspensions(): int
    {
        $queued = 0;

        foreach ($this->scanner->findSuspendableCustomerIds() as $customerId) {
            $customer = Customer::query()->find($customerId);

            if ($customer === null) {
                continue;
            }

            $invoice = $this->oldestOutstandingInvoice($customer);

            SuspendCustomer::dispatch(
                tenantId: (string) $customer->tenant_id,
                customerId: (string) $customerId,
                invoiceId: $invoice?->getKey(),
                reason: $invoice === null ? null : sprintf(
                    'Tagihan %s sebesar Rp %s lewat jatuh tempo %s (%d hari).',
                    $invoice->invoice_number,
                    number_format((float) $invoice->total, 0, ',', '.'),
                    $invoice->due_date->format('d/m/Y'),
                    max(0, (int) $invoice->due_date->diffInDays(now()->startOfDay(), false)),
                ),
            );

            $queued++;
        }

        return $queued;
    }

    /**
     * @return int jumlah pengingat yang dibuat
     */
    private function queueReminders(): int
    {
        $queued = 0;

        foreach ($this->scanner->findDueReminderInvoiceIds() as $invoiceId) {
            $invoice = Invoice::query()->with('customer')->find($invoiceId);

            if ($invoice?->customer === null) {
                continue;
            }

            $this->notifier->notifyDueReminder($invoice->customer, $invoice);

            $queued++;
        }

        return $queued;
    }

    private function oldestOutstandingInvoice(Customer $customer): ?Invoice
    {
        return $customer->invoices()
            ->whereIn('status', ['belum_bayar', 'sebagian', 'terlambat'])
            ->orderBy('due_date')
            ->first();
    }
}
