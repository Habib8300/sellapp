<?php

use App\Filament\Resources\Categories\Pages\ManageCategories;
use App\Filament\Resources\Collections\Pages\ManageCollections;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Category;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Livewire\Livewire;

it('seeds the jersey catalog and demo products idempotently', function (): void {
    $this->seed(CatalogSeeder::class);
    $variant = ProductVariant::query()->firstOrFail();
    $variant->update(['stock_quantity' => 13]);
    $this->seed(CatalogSeeder::class);

    expect(Category::count())->toBe(8)
        ->and(Product::count())->toBe(2)
        ->and(ProductVariant::count())->toBe(20)
        ->and($variant->fresh()->stock_quantity)->toBe(13)
        ->and(Product::where('slug', 'campus-football-jersey')->firstOrFail()->sizeCharts()->count())->toBe(5);
});

it('derives product stock status from active variants and preorder dates', function (): void {
    $category = Category::create([
        'name' => 'Test category',
        'slug' => 'test-category',
    ]);

    $product = Product::create([
        'category_id' => $category->id,
        'name' => 'Test jersey',
        'slug' => 'test-jersey',
        'sku' => 'TEST-JERSEY',
        'price' => 800,
    ]);

    $variant = ProductVariant::create([
        'product_id' => $product->id,
        'sku' => 'TEST-JERSEY-M-BLACK',
        'size' => 'M',
        'color' => 'Black',
        'stock_quantity' => 3,
        'low_stock_threshold' => 5,
    ]);

    expect($product->stock_status)->toBe('low_stock')
        ->and($variant->stockMovements()->count())->toBe(1);

    $variant->update(['stock_quantity' => 0]);
    $product->refresh();

    expect($product->stock_status)->toBe('out_of_stock')
        ->and($variant->stockMovements()->count())->toBe(2);

    $product->update([
        'is_preorder' => true,
        'preorder_close_at' => now()->addDays(3)->toDateString(),
    ]);
    $product->refresh();

    expect($product->stock_status)->toBe('preorder');

    $product->update(['preorder_close_at' => now()->subDay()->toDateString()]);
    $product->refresh();

    expect($product->stock_status)->toBe('out_of_stock');
});

it('renders the product catalog in the authorized admin panel', function (): void {
    $this->seed(RolePermissionSeeder::class);
    $this->seed(CatalogSeeder::class);

    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin)
        ->get('/admin/products')
        ->assertOk()
        ->assertSee('Campus Football Jersey');

    Livewire::test(ManageProducts::class)
        ->mountAction('create')
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('variants')
        ->assertFormFieldExists('images')
        ->assertFormFieldExists('sizeCharts');

    Livewire::test(ManageCategories::class)
        ->mountAction('create')
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('parent_id');

    Livewire::test(ManageCollections::class)
        ->mountAction('create')
        ->assertFormFieldExists('name')
        ->assertFormFieldExists('type');
});
