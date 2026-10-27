<?php

it('includes deployment metadata in the storefront response headers', function (): void {
    $this->get('/')->assertOk()
        ->assertHeader('X-App-Name', config('app.name'))
        ->assertHeader('X-App-Version', config('app.version'))
        ->assertHeader('X-App-Environment', config('app.env'));
});
