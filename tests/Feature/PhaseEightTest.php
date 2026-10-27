<?php

use App\Models\Setting;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;

beforeEach(function (): void {
    $this->seed(RolePermissionSeeder::class);
});

it('renders configured analytics and social links from the store settings', function (): void {
    Setting::query()->updateOrCreate(['key' => 'meta_description'], ['value' => 'Launch-ready jersey store.']);
    Setting::query()->updateOrCreate(['key' => 'google_analytics_id'], ['value' => 'G-ABC123']);
    Setting::query()->updateOrCreate(['key' => 'google_tag_manager_id'], ['value' => 'GTM-XYZ456']);
    Setting::query()->updateOrCreate(['key' => 'facebook_pixel_id'], ['value' => '1234567890']);
    Setting::query()->updateOrCreate(['key' => 'facebook_url'], ['value' => 'https://facebook.com/dhakebir']);
    Setting::query()->updateOrCreate(['key' => 'instagram_url'], ['value' => 'https://instagram.com/dhakebir']);
    Setting::query()->updateOrCreate(['key' => 'whatsapp_number'], ['value' => '+8801712345678']);

    $this->get('/')->assertOk()
        ->assertSee('G-ABC123')
        ->assertSee('GTM-XYZ456')
        ->assertSee('1234567890')
        ->assertSee('https://facebook.com/dhakebir')
        ->assertSee('https://instagram.com/dhakebir')
        ->assertSee('wa.me/8801712345678');
});

it('blocks public storefront access while maintenance mode is enabled and keeps admin users working', function (): void {
    Setting::query()->updateOrCreate(['key' => 'maintenance_mode_enabled'], ['value' => '1']);
    Setting::query()->updateOrCreate(['key' => 'maintenance_message'], ['value' => 'Store is under maintenance, please come back shortly.']);

    $this->get('/')->assertStatus(503)->assertSee('Maintenance')->assertSee('Store is under maintenance');

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)->get('/')->assertOk();
});
