<?php

namespace App\Console\Commands;

use App\Contracts\RouterClient;
use App\Exceptions\RouterOperationFailed;
use App\Mikrotik\RouterClientFactory;
use App\Models\Router;
use App\Tenancy\TenantRunner;
use Illuminate\Console\Command;
use Throwable;

/**
 * Alat bantu saat onboarding router, sebelum UI Pengaturan dari Frontend 1 jadi.
 *
 * Perintah ini tidak mengubah data apa pun, hanya melaporkan hasil tes.
 */
class TestRouterConnectionCommand extends Command
{
    protected $signature = 'isp:router:test
        {router? : Nama router. Kosongkan untuk mencoba semua router aktif.}';

    protected $description = 'Uji koneksi RouterOS ke satu atau seluruh router (beserta jumlah sesi aktif)';

    public function handle(RouterClientFactory $factory, TenantRunner $tenants): int
    {
        $name = $this->argument('router');
        $failed = 0;
        $tested = 0;

        $tenants->forEachActiveTenant(function () use ($name, $factory, &$failed, &$tested): void {
            $query = Router::query()->where('is_active', true)->whereNull('archived_at');

            if ($name !== null) {
                $query->where('name', $name);
            }

            foreach ($query->get() as $router) {
                $tested++;

                if (! $this->report($router, $factory)) {
                    $failed++;
                }
            }
        });

        if ($tested === 0) {
            $this->reportNothingToTest($name);

            return self::FAILURE;
        }

        if ($failed > 0) {
            $this->error("{$failed} dari {$tested} router gagal dites.");

            return self::FAILURE;
        }

        $this->info("{$tested} router online.");

        return self::SUCCESS;
    }

    /**
     * Tanpa router, perintah ini tidak menghasilkan output apa pun, dan itu
     * membingungkan saat onboarding. Karena itu situation-nya dilaporkan
     * eksplisit beserta langkah berikutnya.
     */
    private function reportNothingToTest(?string $name): void
    {
        if ($name !== null) {
            $this->error("Router '{$name}' tidak ditemukan pada tenant aktif mana pun.");

            return;
        }

        $this->error('Belum ada router aktif pada tenant mana pun.');
        $this->comment('Tambahkan router lewat UI Pengaturan, atau isi tabel routers terlebih dahulu.');
    }

    /**
     * @return bool true kalau router online
     */
    private function report(Router $router, RouterClientFactory $factory): bool
    {
        $this->line("→ {$router->name} ({$router->vpn_ip}:{$router->api_port})");

        try {
            $client = $factory->make($router);
            $health = $client->health();
        } catch (Throwable $exception) {
            $this->error('  Gagal: '.$exception->getMessage());

            return false;
        }

        if (! $health->reachable) {
            $this->error('  Offline: '.($health->error ?? 'tanpa keterangan'));

            return false;
        }

        $this->info(sprintf(
            '  Online — identity: %s, versi: %s, uptime: %s, sesi aktif: %s',
            $health->identity ?? '-',
            $health->version ?? '-',
            $health->uptime ?? '-',
            $this->sessionCount($client),
        ));

        return true;
    }

    private function sessionCount(RouterClient $client): string
    {
        try {
            return (string) $client->activeSessionCount();
        } catch (RouterOperationFailed) {
            // User API sering tidak punya policy untuk melihat sesi aktif.
            return 'tidak diizinkan oleh policy';
        } catch (Throwable) {
            return 'tidak diketahui';
        }
    }
}
