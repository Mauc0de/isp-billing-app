<?php

namespace App\Suspension;

use App\Enums\RouterStatus;
use App\Models\Router;
use App\Tenancy\TenantRunner;
use Throwable;

/**
 * Menjalankan pemeriksaan kesehatan router untuk seluruh tenant aktif.
 *
 * Satu router yang error tidak boleh menghentikan pemindaian, jadi exception
 * per router ditangkap dan dilaporkan, lalu dilanjutkan ke router berikutnya.
 * Router yang error dihitung sebagai Unknown, bukan online.
 */
class RouterStatusSyncer
{
    public function __construct(
        private readonly TenantRunner $tenants,
        private readonly RouterStatusMonitor $monitor,
    ) {}

    public function sync(): RouterSyncReport
    {
        $tenants = 0;

        /** @var list<RouterStatus> $statuses */
        $statuses = [];

        $this->tenants->forEachActiveTenant(function () use (&$tenants, &$statuses): void {
            $tenants++;

            Router::query()
                ->where('is_active', true)
                ->whereNull('archived_at')
                ->eachById(function (Router $router) use (&$statuses): void {
                    try {
                        $statuses[] = $this->monitor->refresh($router);
                    } catch (Throwable $exception) {
                        report($exception);

                        $statuses[] = RouterStatus::Unknown;
                    }
                });
        });

        return new RouterSyncReport(tenants: $tenants, statuses: $statuses);
    }
}
