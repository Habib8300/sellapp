<?php

use App\Models\Product;
use App\Models\ProductVariant;
use Database\Seeders\CatalogSeeder;

beforeEach(function (): void {
    $this->seed(CatalogSeeder::class);
});

it('shows featured products and browses the searchable filtered catalog', function (): void {
    $this->get('/')
        ->assertOk()
        ->assertSee('জনপ্রিয় জার্সি')
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি');

    $this->get('/products?q=Football&category=football&size=M&sort=price_asc')
        ->assertOk()
        ->assertSee('ক্যাম্পাস ফুটবল জার্সি')
        ->assertDontSee('ক্যাম্পাস পোলো শার্ট');

    $this->get('/products?availability=invalid')->assertSessionHasErrors('availability');
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
