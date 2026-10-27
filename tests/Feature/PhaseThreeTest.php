<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\HomeHeroSlideSeeder;

beforeEach(function (): void {
    $this->seed(CatalogSeeder::class);
    $this->seed(HomeHeroSlideSeeder::class);
});

it('shows featured products and browses the searchable filtered catalog', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('জনপ্রিয় জার্সি')
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি')
        ->assertSee('home-hero')
        ->assertSee('আপনার ক্যাম্পাস।')
        ->assertSee('জার্সি দেখুন')
        ->assertSee('DCU Jersey House')
        ->assertSee('home-hero__primary', false)
        ->assertSee('home-hero__floating--bottom', false)
        ->assertSee('home-categories', false)
        ->assertSee('home-category-card', false)
        ->assertSee('ক্যাটাগরি অনুযায়ী জার্সি')
        ->assertSee('jersey-glass-card', false)
        ->assertSee('href="/products"', false)
        ->assertSee('শপ');

    $this->get('/products?q=Football&category=football&size=M&sort=price_asc')
        ->assertOk()
        ->assertSee('action="/products"', false)
        ->assertSee('filter-glass-card', false)
        ->assertSee('name="category"', false)
        ->assertSee('name="size"', false)
        ->assertDontSee('<details', false)
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি')
        ->assertDontSee('ক্যাম্পাস পোলো শার্ট');

    $this->get('/products?availability=invalid')->assertSessionHasErrors('availability');
});

it('renders available filter options and applies price ranges to the current selling price', function (): void {
    $this->get('/products')
        ->assertOk()
        ->assertSee('value="Black"', false)
        ->assertSee('value="White"', false)
        ->assertSee('min="850"', false)
        ->assertSee('max="950"', false);

    $this->get('/products?min_price=900&max_price=950')
        ->assertOk()
        ->assertSee('ক্যাম্পাস পোলো শার্ট')
        ->assertDontSee('ক্যাম্পাস ফুটবল জার্সি');
});

it('applies size, color, and stock filters to the same active variant', function (): void {
    $football = Product::query()->where('slug', 'campus-football-jersey')->firstOrFail();
    ProductVariant::query()
        ->where('product_id', $football->id)
        ->where('size', 'M')
        ->where('color', 'Black')
        ->update(['stock_quantity' => 0]);

    $this->get('/products?category=football&size=M&color=black&availability=in_stock')
        ->assertOk()
        ->assertDontSee('ক্যাম্পাস ফুটবল জার্সি');

    $this->get('/products?category=football&size=M&color=black&availability=out_of_stock')
        ->assertOk()
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি');
});

it('shows product details, images fallback, and size chart', function (): void {
    $this->get('/products/campus-football-jersey')
        ->assertOk()
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি')
        ->assertSee('সাইজ চার্ট')
        ->assertSee('Black');
});

it('adds, updates, and removes an available variant from the session cart', function (): void {
    $variant = ProductVariant::query()
        ->whereHas('product', fn ($query) => $query->where('slug', 'campus-football-jersey'))
        ->firstOrFail();

    $this->post('/cart/items', ['variant_id' => $variant->id, 'quantity' => 2])
        ->assertRedirect('/cart');
    $this->get('/cart')
        ->assertOk()
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি')
        ->assertSee('৳1,700');

    $this->patch("/cart/items/{$variant->id}", ['quantity' => 3])
        ->assertRedirect();
    $this->get('/cart')->assertSee('৳2,550');

    $this->delete("/cart/items/{$variant->id}")
        ->assertRedirect();
    $this->get('/cart')->assertSee('আপনার কার্ট এখন খালি');
});

it('rejects unavailable variant purchases and allows preorders', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    $variant->update(['stock_quantity' => 0]);
    $product = $variant->product;

    $this->from("/products/{$product->slug}")
        ->post('/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])
        ->assertRedirect("/products/{$product->slug}")
        ->assertSessionHasErrors('variant_id');

    $product->update(['is_preorder' => true, 'preorder_close_at' => today()->addDays(3)]);

    $this->post('/cart/items', ['variant_id' => $variant->id, 'quantity' => 1])
        ->assertRedirect('/cart');
});

it('toggles products in the guest wishlist', function (): void {
    $product = Product::where('slug', 'campus-football-jersey')->firstOrFail();

    $this->post("/wishlist/{$product->id}")
        ->assertRedirect();
    $this->get('/wishlist')->assertOk()->assertSee('ক্যাম্পাস ফুটবল জার্সি');

    $this->post("/wishlist/{$product->id}")
        ->assertRedirect();
    $this->get('/wishlist')->assertSee('উইশলিস্টে এখনো কোনো পণ্য নেই');
});
