<?php

namespace App\Console\Commands;

use App\Billing\InvoiceGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;

class GenerateInvoicesCommand extends Command
{
    protected $signature = 'isp:generate-invoices {--month= : Bulan acuan (YYYY-MM), default bulan ini}';

    protected $description = 'Buat tagihan bulanan untuk semua pelanggan aktif yang belum punya tagihan di bulan ini';

    public function handle(InvoiceGenerator $generator): int
    {
        $bulan = $this->option('month')
            ? Carbon::createFromFormat('Y-m', (string) $this->option('month'))->startOfMonth()
            : now();

        $created = $generator->run($bulan);

        $this->info("Selesai: {$created} tagihan dibuat untuk bulan {$bulan->translatedFormat('F Y')}.");

        return self::SUCCESS;
    }
}
