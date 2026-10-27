<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class AppHealthCheck extends Command
{
    protected $signature = 'app:health-check {--json : Output the health summary as JSON}';

    protected $description = 'Check that the application, database, cache, and storage are available.';

    public function handle(): int
    {
        $checks = [
            'app' => true,
            'database' => $this->checkDatabase(),
            'cache' => $this->checkCache(),
            'storage' => $this->checkStorage(),
        ];

        $healthy = ! in_array(false, $checks, true);
        $payload = [
            'status' => $healthy ? 'ok' : 'degraded',
            'app' => config('app.name'),
            'environment' => config('app.env'),
            'app_version' => config('app.version', 'dev'),
            'php_version' => PHP_VERSION,
            'laravel_version' => app()->version(),
            'database_connection' => config('database.default'),
            'cache_driver' => config('cache.default'),
            'timestamp' => now()->toIso8601String(),
            'checks' => $checks,
        ];

        if ($this->option('json')) {
            $this->line(json_encode($payload, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

            return $healthy ? self::SUCCESS : self::FAILURE;
        }

        foreach ($checks as $name => $status) {
            $this->info(sprintf('%s: %s', ucfirst($name), $status ? 'ok' : 'failed'));
        }

        $this->info(sprintf('Environment: %s', $payload['environment']));
        $this->info(sprintf('Release: %s', $payload['app_version']));

        return $healthy ? self::SUCCESS : self::FAILURE;
    }

    protected function checkDatabase(): bool
    {
        try {
            DB::connection()->getPdo();

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function checkCache(): bool
    {
        try {
            Cache::put('app-health-check', now()->toIso8601String(), 60);

            return Cache::get('app-health-check') !== null;
        } catch (\Throwable) {
            return false;
        }
    }

    protected function checkStorage(): bool
    {
        try {
            Storage::disk('local')->put('health-check.txt', 'ok');
            Storage::disk('local')->delete('health-check.txt');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }
}
