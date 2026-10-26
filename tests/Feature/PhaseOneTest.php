<?php

use App\Filament\Pages\StoreSettings;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use App\Models\User;
use Database\Seeders\BangladeshLocationSeeder;
use Filament\Facades\Filament;

it('serves the storefront landing page', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('ঢাকেবির জার্সি হাউজ')
        ->assertSee('href="/register"', false)
        ->assertSee('/admin');
});

it('uses Bangla as the default locale', function (): void {
    expect(app()->getLocale())->toBe('bn')
        ->and(__('Register'))->toBe('নিবন্ধন');
});

it('keeps storefront links and assets host-independent', function (): void {
    $this->get('/register')
        ->assertOk()
        ->assertSee('action="/register"', false)
        ->assertSee('href="/login"', false)
        ->assertSee('href="/build/', false)
        ->assertDontSee('127.0.0.1', false);
});

it('uses the forwarded public host for admin assets', function (): void {
    $this->withHeaders([
        'Host' => 'localhost:8000',
        'X-Forwarded-Host' => 'store.example.test',
        'X-Forwarded-Proto' => 'https',
    ])->get('/admin/login')
        ->assertOk()
        ->assertSee('https://store.example.test/css/filament/filament/app.css', false)
        ->assertDontSee('127.0.0.1', false);
});

it('assigns new registrations the customer role', function (): void {
    $this->post('/register', [
        'name' => 'Test Customer',
        'email' => 'customer@example.com',
        'password' => 'password',
        'password_confirmation' => 'password',
    ])->assertRedirect(route('dashboard'));

    expect(User::where('email', 'customer@example.com')->firstOrFail()->hasRole('customer'))->toBeTrue();
});

it('restricts the admin panel to staff roles', function (): void {
    $panel = Filament::getPanel('admin');
    $customer = User::factory()->create();
    $manager = User::factory()->create();
    $manager->assignRole('order_manager');

    expect($customer->canAccessPanel($panel))->toBeFalse()
        ->and($manager->canAccessPanel($panel))->toBeTrue();
});

it('seeds all Bangladesh divisions, districts, and upazilas idempotently', function (): void {
    $this->seed(BangladeshLocationSeeder::class);

    expect(Division::count())->toBe(8)
        ->and(District::count())->toBe(64)
        ->and(Upazila::count())->toBe(491);

    $this->seed(BangladeshLocationSeeder::class);

    expect(Division::count())->toBe(8)
        ->and(District::count())->toBe(64)
        ->and(Upazila::count())->toBe(491);
});

it('limits store settings access to administrators', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $customer = User::factory()->create();
    $customer->assignRole('customer');

    $this->actingAs($admin);
    expect(StoreSettings::canAccess())->toBeTrue();

    $this->actingAs($customer);
    expect(StoreSettings::canAccess())->toBeFalse();
});
