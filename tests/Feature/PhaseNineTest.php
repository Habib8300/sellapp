<?php

it('adds storefront cache headers and includes the sitemap in the robots file', function (): void {
    $this->get('/')->assertOk()->assertHeader('cache-control', 'max-age=300, public, stale-while-revalidate=600');

    $this->get('/robots.txt')->assertOk()->assertSee('sitemap.xml')->assertSee('Disallow: /admin');
});
