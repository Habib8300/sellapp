<?php

use App\Models\Banner;

it('shows image-only homepage banners in an auto-advancing carousel with navigation controls', function (): void {
    foreach (['First banner artwork', 'Second banner artwork', 'Third banner artwork'] as $index => $title) {
        Banner::query()->create([
            'title' => $title,
            'subtitle' => "Hidden banner description {$index}",
            'sort_order' => $index + 1,
            'is_active' => true,
        ]);
    }

    $this->get('/')
        ->assertOk()
        ->assertSee('home-banners', false)
        ->assertSee('home-banner-card', false)
        ->assertSee('aria-label="First banner artwork"', false)
        ->assertSee('aria-label="Third banner artwork"', false)
        ->assertSee('আগের ব্যানার')
        ->assertSee('পরের ব্যানার')
        ->assertSee('window.setInterval(() => this.next(), 10000)', false)
        ->assertDontSee('Hidden banner description');
});
