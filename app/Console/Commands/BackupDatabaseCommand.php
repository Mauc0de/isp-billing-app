<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Process\Process;

class BackupDatabaseCommand extends Command
{
    protected $signature = 'isp:backup-db';

    protected $description = 'Backup database ke storage/app/backups';

    public function handle(): int
    {
        $dir = storage_path('app/backups');

        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $file = $dir.'/backup-'.now()->format('Ymd-His').'.sql';

        $process = new Process([
            'mysqldump',
            '--host='.config('database.connections.mariadb.host'),
            '--port='.config('database.connections.mariadb.port'),
            '--user='.config('database.connections.mariadb.username'),
            '--password='.config('database.connections.mariadb.password'),
            config('database.connections.mariadb.database'),
        ]);

        $process->setTimeout(600);
        $process->run();

        if (! $process->isSuccessful()) {
            $this->error('Backup gagal: '.$process->getErrorOutput());

            return self::FAILURE;
        }

        file_put_contents($file, $process->getOutput());

        // Simpan 7 backup terakhir saja.
        $files = glob($dir.'/backup-*.sql') ?: [];
        rsort($files);

        foreach (array_slice($files, 7) as $old) {
            unlink($old);
        }

        $this->info("Backup tersimpan: {$file}");

        return self::SUCCESS;
    }
}
