<?php

it('exposes a production health endpoint and confirms the app is operational', function (): void {
    $this->get('/health')->assertOk()
        ->assertJsonPath('status', 'ok')
        ->assertJsonPath('checks.database', true)
        ->assertJsonPath('checks.cache', true)
        ->assertJsonPath('checks.storage', true);

    $this->artisan('app:health-check')->assertSuccessful();
});
