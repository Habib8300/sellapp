<?php

use App\Models\District;
use App\Models\Division;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\Upazila;
use Database\Seeders\BangladeshLocationSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\StoreSettingsSeeder;

beforeEach(function (): void {
    $this->seed([
        BangladeshLocationSeeder::class,
        StoreSettingsSeeder::class,
        CatalogSeeder::class,
    ]);
});

function addCheckoutVariant(ProductVariant $variant, int $quantity = 1, array $customization = []): void
{
    test()->post('/cart/items', array_filter([
        'variant_id' => $variant->id,
        'quantity' => $quantity,
        'customization' => $customization,
    ], fn ($value) => $value !== []))->assertRedirect('/cart');
}

function deliveryDetails(District $district): array
{
    $upazila = Upazila::query()->where('district_id', $district->id)->firstOrFail();

    return [
        'fulfillment_method' => 'delivery',
        'division_id' => $district->division_id,
        'district_id' => $district->id,
        'upazila_id' => $upazila->id,
        'delivery_address' => 'House 10, Road 2',
    ];
}

it('places a guest delivery order with customization and creates a downloadable invoice', function (): void {
    $product = Product::where('slug', 'campus-football-jersey')->firstOrFail();
    $product->update(['customization_enabled' => true, 'customization_price' => 150]);
    $variant = $product->variants()->where('size', 'M')->where('color', 'Black')->firstOrFail();
    addCheckoutVariant($variant, 2, [
        'enabled' => '1',
        'name' => 'habib',
        'number' => '23',
        'font' => 'varsity',
        'color' => 'white',
    ]);

    $district = District::query()->where('name', 'Dhaka')->firstOrFail();
    $this->get('/checkout')->assertOk()->assertSee('HABIB');

    $this->post('/checkout', array_merge([
        'customer_name' => 'Test Buyer',
        'customer_email' => 'buyer@example.com',
        'customer_phone' => '01712345678',
        'payment_method' => 'cod',
        'order_note' => 'Please call first.',
    ], deliveryDetails($district)))
        ->assertRedirect();

    $order = Order::query()->with('items')->sole();
    expect($order->subtotal)->toEqual('2000.00')
        ->and($order->delivery_fee)->toEqual('70.00')
        ->and($order->total)->toEqual('2070.00')
        ->and($order->items->sole()->customization)->toMatchArray(['name' => 'HABIB', 'number' => 23])
        ->and($order->payments()->sole()->status)->toBe('pending')
        ->and($variant->fresh()->stock_quantity)->toBe(18);

    $this->get("/orders/{$order->order_number}")
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee('HABIB');

    $invoice = $this->get("/orders/{$order->order_number}/invoice")
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
    expect($invoice->getContent())->toContain('/FontFile2');
});

it('places a campus pickup order and preserves pending manual payment state', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    addCheckoutVariant($variant);

    $this->post('/checkout', [
        'customer_name' => 'Pickup Buyer',
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Dhaka University, TSC',
        'payment_method' => 'bkash',
        'transaction_reference' => 'TXN-123456',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect($order->delivery_fee)->toEqual('0.00')
        ->and($order->payments()->sole()->transaction_reference)->toBe('TXN-123456')
        ->and($order->payment_status)->toBe('pending')
        ->and($order->statusHistory()->sole()->status)->toBe('pending');
});

it('supports a configured advance payment for preorder orders', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    $variant->product->update([
        'is_preorder' => true,
        'preorder_close_at' => today()->addDays(5),
    ]);
    addCheckoutVariant($variant);

    $this->post('/checkout', [
        'customer_name' => 'Advance Buyer',
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'bkash',
        'payment_plan' => 'advance',
    ])->assertRedirect();

    $order = Order::query()->sole();
    expect($order->advance_payment)->toBeTrue()
        ->and($order->total)->toEqual('850.00')
        ->and($order->amount_due_now)->toEqual('425.00')
        ->and($order->payments()->sole()->amount)->toEqual('425.00')
        ->and($variant->fresh()->stock_quantity)->toBe(20);
});

it('rejects stock races and mismatched Bangladesh locations without creating orders', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    $variant->update(['stock_quantity' => 1]);
    addCheckoutVariant($variant, 1);
    $district = District::query()->where('name', 'Dhaka')->firstOrFail();
    $details = deliveryDetails($district);
    $details['district_id'] = District::query()
        ->where('division_id', '!=', $district->division_id)
        ->firstOrFail()
        ->id;

    $this->from('/checkout')->post('/checkout', array_merge([
        'customer_name' => 'Buyer',
        'customer_phone' => '01712345678',
        'payment_method' => 'cod',
    ], $details))->assertRedirect('/checkout')->assertSessionHasErrors('district_id');

    $this->assertDatabaseCount('orders', 0);
    expect($variant->fresh()->stock_quantity)->toBe(1);

    $this->post('/checkout', array_merge([
        'customer_name' => 'Buyer',
        'customer_phone' => '01712345678',
        'payment_method' => 'cod',
    ], deliveryDetails($district)))
        ->assertRedirect();

    expect(Order::count())->toBe(1)
        ->and($variant->fresh()->stock_quantity)->toBe(0);
});

it('does not allow another session to view a guest order or invoice', function (): void {
    $variant = ProductVariant::query()->firstOrFail();
    addCheckoutVariant($variant);
    $this->post('/checkout', [
        'customer_name' => 'Buyer',
        'customer_phone' => '01712345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
    ])->assertRedirect();

    $order = Order::query()->sole();
    $this->flushSession();
    $this->get("/orders/{$order->order_number}")->assertForbidden();
    $this->get("/orders/{$order->order_number}/invoice")->assertForbidden();
});

it('loads dependent Bangladesh checkout locations and requires valid checkout data', function (): void {
    $division = Division::query()->where('name', 'Dhaka')->firstOrFail();
    $district = District::query()->where('division_id', $division->id)->firstOrFail();

    $this->get("/locations/divisions/{$division->id}/districts")
        ->assertOk()
        ->assertJsonFragment(['id' => $district->id]);
    $this->get("/locations/districts/{$district->id}/upazilas")
        ->assertOk()
        ->assertJsonFragment(['id' => Upazila::query()->where('district_id', $district->id)->firstOrFail()->id]);

    $this->post('/checkout', [
        'customer_name' => 'Buyer',
        'customer_phone' => '123',
        'fulfillment_method' => 'delivery',
        'payment_method' => 'cod',
    ])->assertSessionHasErrors(['customer_phone', 'division_id', 'district_id', 'upazila_id', 'delivery_address']);

    $this->assertDatabaseCount('orders', 0);
});

it('requires customization fields and keeps separately customized same-size cart lines distinct', function (): void {
    $product = Product::where('slug', 'campus-football-jersey')->firstOrFail();
    $product->update(['customization_enabled' => true, 'customization_price' => 100]);
    $variant = $product->variants()->where('size', 'M')->where('color', 'Black')->firstOrFail();

    $this->from('/products/campus-football-jersey')
        ->post('/cart/items', [
            'variant_id' => $variant->id,
            'quantity' => 1,
            'customization' => ['enabled' => '1', 'name' => 'Player'],
        ])
        ->assertRedirect('/products/campus-football-jersey')
        ->assertSessionHasErrors('customization.number');

    addCheckoutVariant($variant, 1, [
        'enabled' => '1',
        'name' => 'Player',
        'number' => '7',
        'font' => 'classic',
        'color' => 'gold',
    ]);
    addCheckoutVariant($variant, 1, [
        'enabled' => '1',
        'name' => 'Captain',
        'number' => '10',
        'font' => 'varsity',
        'color' => 'white',
    ]);

    $this->get('/cart')
        ->assertOk()
        ->assertSee('PLAYER')
        ->assertSee('CAPTAIN')
        ->assertSee('৳1,900');
});
