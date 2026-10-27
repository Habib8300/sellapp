<?php

use Database\Seeders\CmsPageSeeder;
use Illuminate\Support\Facades\DB;

it('publishes the About page and displays configured contact details in the footer', function (): void {
    $this->seed(CmsPageSeeder::class);

    DB::table('settings')->updateOrInsert(
        ['key' => 'support_phone'],
        ['value' => '+8801700000000'],
    );
    DB::table('settings')->updateOrInsert(
        ['key' => 'support_email'],
        ['value' => 'support@example.com'],
    );
    DB::table('settings')->updateOrInsert(
        ['key' => 'brand_primary_color'],
        ['value' => '#123456'],
    );

    $this->get('/pages/about')
        ->assertOk()
        ->assertSee('আমাদের সম্পর্কে')
        ->assertSee('DCU Jersey House')
        ->assertSee('<title>আমাদের সম্পর্কে | DCU Jersey House</title>', false)
        ->assertSee('class="store-footer"', false)
        ->assertSee('style="--store-brand: #123456"', false)
        ->assertSee('যোগাযোগ করুন')
        ->assertSee('href="tel:+8801700000000"', false)
        ->assertSee('href="mailto:support@example.com"', false);
});
