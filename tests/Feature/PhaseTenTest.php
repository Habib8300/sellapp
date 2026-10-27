<?php

it('creates a database backup and prunes expired backup files', function (): void {
    config()->set('database.default', 'sqlite');
    config()->set('database.connections.sqlite.database', database_path('database.sqlite'));

    $backupDir = storage_path('app/backups');
    @mkdir($backupDir, 0775, true);

    $oldBackup = $backupDir.'/db-sqlite-'.now()->subDays(10)->format('Ymd_His').'.sql';
    file_put_contents($oldBackup, 'stale database dump');

    $this->artisan('app:db-backup')->assertSuccessful();

    $this->assertNotEmpty(glob($backupDir.'/*.sql'));

    $this->artisan('app:prune-db-backups')->assertSuccessful();

    $currentBackups = glob($backupDir.'/*.sql');
    expect($currentBackups)->not->toBeEmpty();
    expect(array_filter($currentBackups, fn (string $path) => str_contains($path, 'db-sqlite-')))->not->toBeEmpty();
});
