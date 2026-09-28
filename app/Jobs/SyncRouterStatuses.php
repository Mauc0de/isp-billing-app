<?php

namespace App\Jobs;

use App\Suspension\RouterStatusSyncer;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;

/**
 * Mengecek kesehatan seluruh router yang aktif dan menyimpan statusnya.
 *
 * Satu router yang timeout tidak boleh menghentikan pemindaian, jadi exception
 * per router ditangani di RouterStatusSyncer.
 */
class SyncRouterStatuses implements ShouldQueue
{
    use Queueable;

    public int $tries = 1;

    public function __construct()
    {
        $this->queue = 'automation';
    }

    public function handle(RouterStatusSyncer $syncer): void
    {
        $syncer->sync();
    }
}
