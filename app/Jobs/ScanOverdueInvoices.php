<?php

namespace App\Jobs;

use App\Suspension\OverdueScanRunner;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Pemindaian harian seluruh tenant: suspend yang terlewat + pengingat jatuh tempo.
 *
 * Job ini dijalankan sekali oleh scheduler tanpa konteks tenant, jadi ia
 * meneruskan ke OverdueScanRunner yang mengiterasi tenant aktif sendiri.
 * Isi logikanya ada di sana, bukan di sini, supaya bisa dipanggil langsung
 * oleh command isp:scan-overdue tanpa harus lewat queue.
 */
class ScanOverdueInvoices implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct(
        public bool $suspend = true,
        public bool $remind = true,
    ) {
        $this->queue = 'automation';
    }

    public function handle(OverdueScanRunner $runner): void
    {
        $runner->run($this->suspend, $this->remind);
    }
}
