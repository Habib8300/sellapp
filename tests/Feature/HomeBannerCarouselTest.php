<?php

use App\Models\Banner;

it('pages homepage banners in desktop pairs and mobile singles every five seconds', function (): void {
    foreach (['First banner artwork', 'Second banner artwork', 'Third banner artwork', 'Fourth banner artwork'] as $index => $title) {
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
        ->assertSee('home-banners__grid', false)
        ->assertSee('home-banner-card', false)
        ->assertSee('aria-label="First banner artwork — 1 / 4"', false)
        ->assertSee('aria-label="Second banner artwork — 2 / 4"', false)
        ->assertSee('aria-label="Third banner artwork — 3 / 4"', false)
        ->assertSee('aria-label="Fourth banner artwork — 4 / 4"', false)
        ->assertSee('visibleCount: window.matchMedia', false)
        ->assertSee("window.matchMedia('(max-width: 700px)').matches ? 1 : 2", false)
        ->assertSee('Math.ceil(this.slideCount / this.visibleCount)', false)
        ->assertSee('window.setInterval(() => this.next(), 5000)', false)
        ->assertSee('home-banners__controls', false)
        ->assertDontSee('Hidden banner description');
});
