<?php

namespace App\Console\Commands;

use App\Suspension\OverdueScanRunner;
use App\Suspension\ScanReport;
use Illuminate\Console\Command;

class ScanOverdueInvoicesCommand extends Command
{
    protected $signature = 'isp:scan-overdue
        {--suspend-only : Lewati pengingat jatuh tempo}
        {--remind-only : Lewati suspend}';

    protected $description = 'Pindai tagihan terlambat & jatuh tempo, lalu antrekan aksi suspend dan notifikasi WhatsApp';

    public function handle(OverdueScanRunner $runner): int
    {
        $suspend = ! $this->option('remind-only');
        $remind = ! $this->option('suspend-only');

        if (! $suspend && ! $remind) {
            $this->error('Pilih minimal salah satu dari --suspend-only atau --remind-only.');

            return self::FAILURE;
        }

        $report = $runner->run($suspend, $remind);

        if ($report->foundNothing()) {
            $this->info("Pemindaian selesai: tidak ada tagihan yang perlu ditindak ({$report->tenants} tenant diperiksa).");

            return self::SUCCESS;
        }

        $this->info('Pemindaian selesai.');
        $this->renderReport($report);

        return self::SUCCESS;
    }

    private function renderReport(ScanReport $report): void
    {
        $this->table(
            ['Tenant', 'Auto-renew', 'Antrean suspend', 'Pengingat jatuh tempo'],
            [[$report->tenants, $report->renewed, $report->suspended, $report->reminded]],
        );

        $this->line($report->summary());
        $this->comment('Aksi suspend dijalankan oleh worker queue pada queue "automation".');
    }
}
