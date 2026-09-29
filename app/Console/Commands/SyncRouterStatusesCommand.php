<?php

namespace App\Console\Commands;

use App\Suspension\RouterStatusSyncer;
use Illuminate\Console\Command;

class SyncRouterStatusesCommand extends Command
{
    protected $signature = 'isp:sync-routers';

    protected $description = 'Cek konektivitas seluruh router aktif dan perbarui statusnya';

    public function handle(RouterStatusSyncer $syncer): int
    {
        $report = $syncer->sync();

        if ($report->foundNothing()) {
            $this->warn("Tidak ada router aktif untuk diperiksa ({$report->tenants} tenant diperiksa).");
            $this->comment('Tambahkan router lewat UI Pengaturan, atau aktifkan router yang ada di tabel routers.');

            return self::SUCCESS;
        }

        $this->info($report->summary());

        return $report->offline() > 0 ? self::FAILURE : self::SUCCESS;
    }
}
