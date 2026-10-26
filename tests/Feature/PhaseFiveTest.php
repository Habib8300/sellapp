<?php

use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteRequest;
use App\Models\User;
use Database\Seeders\BangladeshLocationSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StoreSettingsSeeder;

beforeEach(function (): void {
    $this->seed([
        BangladeshLocationSeeder::class,
        StoreSettingsSeeder::class,
        RolePermissionSeeder::class,
        CatalogSeeder::class,
    ]);
});

function addPhaseFiveCart(ProductVariant $variant, int $quantity, array $customization = []): void
{
    test()->post('/cart/items', array_filter([
        'variant_id' => $variant->id,
        'quantity' => $quantity,
        'customization' => $customization,
    ], fn ($value) => $value !== []))->assertRedirect('/cart');
}

it('applies the best bulk slab and a valid coupon to order totals', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    addPhaseFiveCart($variant, 10);
    Coupon::create([
        'code' => 'TEAM10',
        'type' => 'percent',
        'amount' => 10,
        'minimum_order' => 100,
        'usage_limit' => 1,
        'is_active' => true,
    ]);

    $this->post('/checkout', [
        'customer_name' => 'Bulk Buyer',
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
        'coupon_code' => 'team10',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect((float) $order->subtotal)->toBe(8500.0)
        ->and((float) $order->discount_amount)->toBe(1232.5)
        ->and((float) $order->total)->toBe(7267.5)
        ->and($order->coupon->code)->toBe('TEAM10')
        ->and($order->coupon->used_count)->toBe(1);
});

it('rejects student-only coupons unless the signed-in customer is verified', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    addPhaseFiveCart($variant, 1);
    Coupon::create([
        'code' => 'STUDENT',
        'type' => 'fixed',
        'amount' => 100,
        'student_only' => true,
        'is_active' => true,
    ]);

    $this->from('/checkout')->post('/checkout', [
        'customer_name' => 'Guest',
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
        'coupon_code' => 'STUDENT',
    ])->assertRedirect('/checkout')->assertSessionHasErrors('coupon_code');
    $this->assertDatabaseCount('orders', 0);

    $user = User::factory()->create(['student_verified' => true]);
    $user->assignRole('customer');
    $this->actingAs($user)->post('/checkout', [
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
        'coupon_code' => 'STUDENT',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect($order->coupon->code)->toBe('STUDENT')
        ->and((float) $order->discount_amount)->toBe(100.0);
});

it('applies the configured student discount to verified customers without a coupon', function (): void {
    $user = User::factory()->create(['student_verified' => true]);
    $user->assignRole('customer');
    $variant = ProductVariant::query()->firstOrFail();
    addPhaseFiveCart($variant, 1);

    $this->actingAs($user)->post('/checkout', [
        'customer_name' => $user->name,
        'customer_email' => $user->email,
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
    ])->assertRedirect();

    expect((float) Order::query()->sole()->discount_amount)->toBe(42.5);
});

it('accepts student verification requests for manual admin review', function (): void {
    $user = User::factory()->create();
    $user->assignRole('customer');

    $this->actingAs($user)->get('/student-verification')->assertOk();
    $this->post('/student-verification', [
        'student_institution' => 'Dhaka University',
        'student_email' => 'student@du.ac.bd',
    ])->assertRedirect('/student-verification');

    expect($user->fresh()->student_institution)->toBe('Dhaka University')
        ->and($user->fresh()->student_email)->toBe('student@du.ac.bd')
        ->and($user->fresh()->student_verification_submitted_at)->not->toBeNull()
        ->and($user->fresh()->student_verified)->toBeFalse();
});

it('tracks campaign participants and enforces the campaign product', function (): void {
    $product = Product::query()->firstOrFail();
    $campaign = Campaign::create([
        'name' => 'Department Batch',
        'slug' => 'department-batch',
        'department' => 'CSE',
        'batch' => '23rd',
        'product_id' => $product->id,
        'deadline' => now()->addDay(),
        'is_active' => true,
    ]);
    $variant = $product->variants()->firstOrFail();
    addPhaseFiveCart($variant, 2);

    $this->get('/campaigns/department-batch')->assertOk()->assertSee('23rd');
    $this->post('/checkout', [
        'customer_name' => 'Campaign Buyer',
        'customer_email' => 'campaign@example.com',
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
    ])->assertRedirect();

    expect($campaign->campaignOrders()->sole()->quantity)->toBe(2)
        ->and(Order::query()->sole()->campaign_id)->toBe($campaign->id);
});

it('accepts team-order rows and provides a 50-piece quote request form', function (): void {
    $product = Product::query()->firstOrFail();
    $variant = $product->variants()->firstOrFail();
    $product->update(['customization_enabled' => true]);

    $this->get('/team-orders')->assertOk()->assertSee('CSV আপলোড');
    $this->post('/team-orders', [
        'product_id' => $product->id,
        'rows' => [
            ['variant_id' => $variant->id, 'name' => 'Member', 'number' => 10],
            ['variant_id' => $variant->id, 'name' => 'Another', 'number' => 11],
        ],
    ])->assertRedirect('/cart');
    expect(session('cart'))->toHaveCount(2);

    $this->get('/bulk-quote')->assertOk()->assertSee('৫০+ পিসের কোট');
    $this->post('/bulk-quote', [
        'name' => 'Team Contact',
        'phone' => '01812345678',
        'quantity' => 50,
        'department' => 'CSE',
    ])->assertRedirect('/bulk-quote');
    expect(QuoteRequest::query()->sole()->status)->toBe('pending');
});

it('exports production rows only for authorized staff', function (): void {
    $product = Product::query()->firstOrFail();
    $product->update(['customization_enabled' => true]);
    $variant = $product->variants()->firstOrFail();
    addPhaseFiveCart($variant, 1, [
        'enabled' => '1',
        'name' => 'PRINTER',
        'number' => '7',
        'font' => 'classic',
        'color' => 'white',
    ]);
    $this->post('/checkout', [
        'customer_name' => 'Production Buyer',
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
    ])->assertRedirect();

    $order = Order::query()->sole();
    $order->update(['status' => 'in_production']);
    $staff = User::factory()->create();
    $staff->assignRole('order_manager');
    $export = $this->actingAs($staff)->get('/admin/production-sheet.csv')
        ->assertOk()
        ->assertHeader('content-type', 'text/csv; charset=UTF-8');
    expect($export->streamedContent())->toContain('PRINTER');

    auth()->logout();
    $this->get('/admin/production-sheet.csv')->assertRedirect('/login');
});

it('renders the phase five admin workflows for staff', function (): void {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    $this->actingAs($admin);
    foreach (['orders', 'campaigns', 'coupons', 'bulk-discount-slabs', 'quote-requests', 'student-verifications', 'production-sheet'] as $path) {
        $this->get('/admin/'.$path)->assertOk();
    }
});
