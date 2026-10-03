<?php

namespace App\Console\Commands;

use App\Mikrotik\RouterClientFactory;
use App\Models\Pelanggan;
use App\Models\Router;
use App\Tenancy\TenantRunner;
use Illuminate\Console\Command;
use Throwable;

class SyncPppoeCommand extends Command
{
    protected $signature = 'isp:sync-pppoe {--tenant= : Hanya untuk satu tenant}';

    protected $description = 'Sinkronkan user PPPoE pelanggan ke router Mikrotik (buat/update PPP secret)';

    public function handle(RouterClientFactory $factory, TenantRunner $tenants): int
    {
        $synced = 0;
        $failed = 0;

        $tenants->forEachActiveTenant(function () use ($factory, &$synced, &$failed): void {
            Pelanggan::query()
                ->whereNotNull('router_id')
                ->whereNotNull('mikrotik_username')
                ->whereNotNull('pppoe_password')
                ->with(['paket', 'router'])
                ->each(function (Pelanggan $pelanggan) use ($factory, &$synced, &$failed): void {
                    $router = $pelanggan->router;

                    if (! $router instanceof Router || ! $router->is_active) {
                        return;
                    }

                    try {
                        $factory->make($router)->ensurePppSecret(
                            username: (string) $pelanggan->mikrotik_username,
                            password: (string) $pelanggan->pppoe_password,
                            profile: $pelanggan->paket?->nama_paket ?? 'default',
                            disabled: $pelanggan->status->value !== 'aktif',
                        );

                        $synced++;
                    } catch (Throwable $e) {
                        $failed++;
                        $this->warn("Gagal sync {$pelanggan->nama}: {$e->getMessage()}");
                    }
                });
        });

        $this->info("Sinkronisasi PPPoE selesai: {$synced} berhasil, {$failed} gagal.");

        return $failed > 0 ? self::FAILURE : self::SUCCESS;
    }
}
