<?php

namespace App\Console\Commands;

use App\Mikrotik\RouterClientFactory;
use App\Models\Router;
use App\Models\Voucher;
use App\Tenancy\TenantRunner;
use Illuminate\Console\Command;
use Throwable;

class SyncVouchersCommand extends Command
{
    protected $signature = 'isp:sync-vouchers';

    protected $description = 'Upload voucher yang belum dipakai ke user hotspot di semua router aktif';

    public function handle(RouterClientFactory $factory, TenantRunner $tenants): int
    {
        $synced = 0;
        $failed = 0;

        $tenants->forEachActiveTenant(function () use ($factory, &$synced, &$failed): void {
            $routers = Router::query()->where('is_active', true)->whereNull('archived_at')->get();

            if ($routers->isEmpty()) {
                return;
            }

            $vouchers = Voucher::query()
                ->where('status', 'belum_dipakai')
                ->with('paket')
                ->get();

            foreach ($routers as $router) {
                foreach ($vouchers as $voucher) {
                    try {
                        $factory->make($router)->ensureHotspotUser(
                            username: (string) $voucher->kode,
                            password: (string) $voucher->kode,
                            profile: $voucher->paket?->nama_paket ?? 'default',
                        );

                        $synced++;
                    } catch (Throwable $e) {
                        $failed++;
                        $this->warn("Gagal upload {$voucher->kode} ke {$router->name}: {$e->getMessage()}");
                    }
                }
            }
        });

        $this->info("Sinkronisasi voucher selesai: {$synced} upload, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
