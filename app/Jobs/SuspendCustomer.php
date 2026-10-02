<?php

namespace App\Jobs;

use App\Enums\SuspensionSource;
use App\Models\Pelanggan;
use App\Models\Tagihan;
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
        $pelanggan = Pelanggan::query()->find($this->customerId);

        if ($pelanggan === null) {
            return;
        }

        $this->resolve(CustomerSuspender::class)->suspend(
            pelanggan: $pelanggan,
            source: SuspensionSource::Overdue,
            tagihan: $this->invoiceId === null
                ? null
                : Tagihan::query()->find($this->invoiceId),
            reason: $this->reason,
        );
    }
}
