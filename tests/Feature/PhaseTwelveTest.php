<?php

it('includes release metadata in the health reports and CLI output', function (): void {
    $this->get('/health')
        ->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('app_version', config('app.version'))
        ->assertJsonPath('environment', config('app.env'))
        ->assertJsonPath('database_connection', config('database.default'))
        ->assertJsonPath('cache_driver', config('cache.default'));

    $this->artisan('app:health-check --json')->assertSuccessful();
});
