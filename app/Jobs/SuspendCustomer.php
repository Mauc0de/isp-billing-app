<?php

namespace App\Jobs;

use App\Enums\SuspensionSource;
use App\Models\Customer;
use App\Models\Invoice;
use App\Suspension\CustomerSuspender;

/**
 * Menjalankan suspend satu pelanggan.
 *
 * Yang diserialisasi hanya ID, bukan model Eloquent, karena model harus
 * diambil ulang di dalam konteks tenant yang benar.
 */
class SuspendCustomer extends TenantJob
{
    public int $tries = 2;

    public function __construct(
        string $tenantId,
        public string $customerId,
        public ?string $invoiceId = null,
        public ?string $reason = null,
    ) {
        parent::__construct($tenantId);
    }

    protected function handleInTenant(): void
    {
        $customer = Customer::query()->find($this->customerId);

        if ($customer === null) {
            return;
        }

        $this->resolve(CustomerSuspender::class)->suspend(
            customer: $customer,
            source: SuspensionSource::Overdue,
            invoice: $this->invoiceId === null
                ? null
                : Invoice::query()->find($this->invoiceId),
            reason: $this->reason,
        );
    }
}
