<?php

use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Filament\Resources\HomeHeroSlides\Pages\ManageHomeHeroSlides;
use App\Models\Banner;
use App\Models\HomeHeroSlide;
use App\Models\User;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

it('shows active welcome slides in admin-defined order and leaves deleted or disabled slides off the homepage', function (): void {
    HomeHeroSlide::query()->create([
        'title' => 'দ্বিতীয় স্বাগতম স্লাইড',
        'subtitle' => 'দ্বিতীয় বর্ণনা',
        'button_text' => 'দেখুন',
        'button_url' => '/products',
        'sort_order' => 20,
        'is_active' => true,
    ]);

    HomeHeroSlide::query()->create([
        'title' => 'প্রথম স্বাগতম স্লাইড',
        'subtitle' => 'প্রথম বর্ণনা',
        'button_text' => 'শুরু করুন',
        'sort_order' => 10,
        'is_active' => true,
    ]);

    HomeHeroSlide::query()->create([
        'title' => 'লুকানো স্লাইড',
        'button_text' => 'লুকানো',
        'sort_order' => 5,
        'is_active' => false,
    ]);

    $response = $this->get('/')
        ->assertOk()
        ->assertSee('প্রথম স্বাগতম স্লাইড')
        ->assertSee('দ্বিতীয় স্বাগতম স্লাইড')
        ->assertDontSee('লুকানো স্লাইড')
        ->assertSee('home-hero__controls', false)
        ->assertSee('পরের স্লাইড');

    expect($response->headers->get('Cache-Control'))
        ->toContain('no-store')
        ->and(strpos($response->getContent(), 'প্রথম স্বাগতম স্লাইড'))
        ->toBeLessThan(strpos($response->getContent(), 'দ্বিতীয় স্বাগতম স্লাইড'));
});

it('keeps the first welcome slide visible until Alpine initializes', function (): void {
    HomeHeroSlide::query()->create([
        'title' => 'প্রথম স্বাগতম স্লাইড',
        'button_text' => 'শুরু করুন',
        'sort_order' => 1,
        'is_active' => true,
    ]);

    HomeHeroSlide::query()->create([
        'title' => 'দ্বিতীয় স্বাগতম স্লাইড',
        'button_text' => 'দেখুন',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    $html = $this->get('/')->assertOk()->getContent();

    expect($html)
        ->toMatch('/<div\s+class="home-hero__slide"\s+x-show="activeSlide === 0"/')
        ->toMatch('/<div\s+class="home-hero__slide"\s+x-cloak\s+x-show="activeSlide === 1"/');
});

it('lets super admins manage welcome slides in Filament', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $this->actingAs($admin)
        ->get('/admin/home-hero-slides')
        ->assertOk()
        ->assertSee('Welcome page slides')
        ->assertSee('Create');
});

it('moves welcome slides up and down while keeping their display order contiguous', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('super_admin');

    $first = HomeHeroSlide::query()->create([
        'title' => 'প্রথম স্লাইড',
        'button_text' => 'দেখুন',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $second = HomeHeroSlide::query()->create([
        'title' => 'দ্বিতীয় স্লাইড',
        'button_text' => 'দেখুন',
        'sort_order' => 2,
        'is_active' => true,
    ]);
    $third = HomeHeroSlide::query()->create([
        'title' => 'তৃতীয় স্লাইড',
        'button_text' => 'দেখুন',
        'sort_order' => 3,
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ManageHomeHeroSlides::class)
        ->assertTableActionExists('moveUp', null, $second)
        ->assertTableActionExists('moveDown', null, $second)
        ->callTableAction('moveDown', $first);

    expect(HomeHeroSlide::query()->orderBy('sort_order')->pluck('id')->all())
        ->toBe([$second->id, $first->id, $third->id]);
    expect(HomeHeroSlide::query()->orderBy('sort_order')->pluck('sort_order')->all())
        ->toBe([1, 2, 3]);
});

it('lets admins move homepage banner posts up and down', function (): void {
    $this->seed(RolePermissionSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $first = Banner::query()->create([
        'title' => 'প্রথম ব্যানার',
        'sort_order' => 1,
        'is_active' => true,
    ]);
    $second = Banner::query()->create([
        'title' => 'দ্বিতীয় ব্যানার',
        'sort_order' => 2,
        'is_active' => true,
    ]);

    Livewire::actingAs($admin)
        ->test(ManageBanners::class)
        ->assertTableActionExists('moveUp', null, $second)
        ->assertTableActionExists('moveDown', null, $first)
        ->callTableAction('moveUp', $second);

    expect(Banner::query()->orderBy('sort_order')->pluck('id')->all())
        ->toBe([$second->id, $first->id]);
});
