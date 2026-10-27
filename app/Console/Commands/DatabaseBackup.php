<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class DatabaseBackup extends Command
{
    protected $signature = 'app:db-backup';

    protected $description = 'Create a database backup in storage/app/backups';

    public function handle(): int
    {
        $connection = config('database.default');
        $directory = storage_path('app/backups');

        if (! is_dir($directory) && ! mkdir($directory, 0775, true) && ! is_dir($directory)) {
            $this->error('Unable to create backup directory.');

            return self::FAILURE;
        }

        $timestamp = now()->format('Ymd_His');
        $target = $directory.'/db-'.$connection.'-'.$timestamp.'.sql';

        if ($connection === 'sqlite') {
            $databasePath = config('database.connections.sqlite.database') ?? database_path('database.sqlite');

            if (! file_exists($databasePath)) {
                $this->error('SQLite database file not found.');

                return self::FAILURE;
            }

            $command = sprintf('sqlite3 %s ".backup %s"', escapeshellarg($databasePath), escapeshellarg($target));
            exec($command, $output, $exitCode);

            if ($exitCode !== 0 && file_exists($databasePath)) {
                $copied = copy($databasePath, $target);
                if (! $copied) {
                    $this->error('Failed to create SQLite backup.');

                    return self::FAILURE;
                }
            } elseif ($exitCode !== 0) {
                $this->error('Failed to create SQLite backup.');

                return self::FAILURE;
            }
        } else {
            $config = config('database.connections.'.$connection, []);
            $host = $config['host'] ?? '127.0.0.1';
            $port = $config['port'] ?? 3306;
            $database = $config['database'] ?? null;
            $username = $config['username'] ?? null;
            $password = $config['password'] ?? '';

            if (! $database) {
                $this->error('Database name is not configured for the active connection.');

                return self::FAILURE;
            }

            $cmd = sprintf(
                'mysqldump --host=%s --port=%s --user=%s --password=%s --single-transaction --routines --triggers --events %s > %s',
                escapeshellarg($host),
                escapeshellarg((string) $port),
                escapeshellarg($username ?? 'root'),
                escapeshellarg($password),
                escapeshellarg($database),
                escapeshellarg($target),
            );

            exec($cmd, $output, $exitCode);

            if ($exitCode !== 0) {
                $this->error('Failed to create MySQL backup.');

                return self::FAILURE;
            }
        }

        if (file_exists($target)) {
            $this->info('Database backup created: '.$target);

            return self::SUCCESS;
        }

        $this->error('Backup file was not created.');

        return self::FAILURE;
    }
}
