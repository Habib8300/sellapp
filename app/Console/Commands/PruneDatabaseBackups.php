<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PruneDatabaseBackups extends Command
{
    protected $signature = 'app:prune-db-backups';

    protected $description = 'Remove expired database backup files older than the configured retention';

    public function handle(): int
    {
        $retentionDays = max(1, (int) env('DB_BACKUP_RETENTION_DAYS', 7));
        $directory = storage_path('app/backups');

        if (! is_dir($directory)) {
            $this->info('No backup directory found.');

            return self::SUCCESS;
        }

        $deleted = 0;
        $cutoff = now()->subDays($retentionDays)->getTimestamp();

        foreach (glob($directory.'/*') ?: [] as $file) {
            if (is_file($file) && filemtime($file) < $cutoff) {
                unlink($file);
                $deleted++;
            }
        }

        $this->info(sprintf('Deleted %d backup file(s).', $deleted));

        return self::SUCCESS;
    }
}
