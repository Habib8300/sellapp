<?php

it('adds the baseline security headers used by the storefront', function (): void {
    $this->get('/')->assertOk()
        ->assertHeader('x-frame-options', 'DENY')
        ->assertHeader('x-content-type-options', 'nosniff')
        ->assertHeader('referrer-policy', 'strict-origin-when-cross-origin')
        ->assertHeader('cross-origin-resource-policy', 'same-origin')
        ->assertHeader('x-permitted-cross-domain-policies', 'none');
});

it('serves the production deployment checklist and project security guidance', function (): void {
    expect(file_exists(base_path('DEPLOYMENT.md')))->toBeTrue()
        ->and(file_exists(base_path('SECURITY.md')))->toBeTrue();
});
