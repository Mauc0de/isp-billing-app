<?php

namespace App\Suspension;

use App\Enums\RouterStatus;
use App\Mikrotik\RouterClientFactory;
use App\Models\Router;
use Illuminate\Support\Carbon;

/**
 * Memeriksa kesehatan setiap router lalu menyimpan hasilnya.
 *
 * Router yang lama tidak terjangkau otomatis dinonaktifkan supaya tidak
 * dicoba setiap hari selamanya dan supaya tidak dipakai untuk suspend pelanggan.
 */
class RouterStatusMonitor
{
    public function __construct(private readonly RouterClientFactory $routers) {}

    public function refresh(Router $router): RouterStatus
    {
        $health = $this->routers->make($router)->health();
        $status = $health->reachable ? RouterStatus::Online : RouterStatus::Offline;

        $attributes = [
            'status' => $status,
            'last_error' => $health->error,
        ];

        if ($health->reachable) {
            $attributes['last_seen_at'] = now();
        }

        if ($status === RouterStatus::Offline && $this->offlineForTooLong($router)) {
            $attributes['is_active'] = false;
        }

        $router->forceFill($attributes)->save();

        return $status;
    }

    private function offlineForTooLong(Router $router): bool
    {
        if ($router->last_seen_at === null) {
            // Tidak pernah terlihat sama sekali: biarkan, mungkin baru ditambahkan.
            return false;
        }

        $threshold = (int) config('mikrotik.offline_after_days');

        if ($threshold <= 0) {
            return false;
        }

        return $router->last_seen_at->lt(Carbon::now()->subDays($threshold));
    }
}
