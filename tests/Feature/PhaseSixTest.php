<?php

use App\Jobs\SendOrderStatusEmail;
use App\Jobs\SendOrderStatusSms;
use App\Mail\OrderStatusMail;
use App\Models\Address;
use App\Models\Banner;
use App\Models\BlogPost;
use App\Models\CmsPage;
use App\Models\District;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\Setting;
use App\Models\Shipment;
use App\Models\User;
use Database\Seeders\BangladeshLocationSeeder;
use Database\Seeders\CatalogSeeder;
use Database\Seeders\CmsPageSeeder;
use Database\Seeders\RolePermissionSeeder;
use Database\Seeders\StoreSettingsSeeder;
use Illuminate\Http\Client\Request as ClientRequest;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Mail;

beforeEach(function (): void {
    $this->seed([
        BangladeshLocationSeeder::class,
        StoreSettingsSeeder::class,
        RolePermissionSeeder::class,
        CatalogSeeder::class,
    ]);
});

function createPhaseSixOrder(User $buyer): Order
{
    test()->actingAs($buyer)->post('/cart/items', [
        'variant_id' => ProductVariant::query()->firstOrFail()->id,
        'quantity' => 1,
    ])->assertRedirect('/cart');

    test()->post('/checkout', [
        'customer_name' => $buyer->name,
        'customer_email' => $buyer->email,
        'customer_phone' => '01812345678',
        'fulfillment_method' => 'campus_pickup',
        'pickup_point' => 'Campus',
        'payment_method' => 'cod',
    ])->assertRedirect();

    return Order::query()->latest('id')->firstOrFail();
}

function markPhaseSixOrderDelivered(Order $order, int $daysAgo = 0): void
{
    $order->update(['status' => 'delivered']);
    $order->statusHistory()->create([
        'status' => 'delivered',
        'created_at' => now()->subDays($daysAgo),
    ]);
}

it('isolates account data and saves only location-consistent customer addresses', function (): void {
    $owner = User::factory()->create();
    $owner->assignRole('customer');
    $other = User::factory()->create();
    $other->assignRole('customer');
    $order = createPhaseSixOrder($owner);

    $district = District::query()->where('name', 'Dhaka')->firstOrFail();
    $upazila = $district->upazilas()->firstOrFail();
    $addressData = [
        'label' => 'Home',
        'recipient_name' => $owner->name,
        'phone' => '01812345678',
        'division_id' => $district->division_id,
        'district_id' => $district->id,
        'upazila_id' => $upazila->id,
        'address' => 'House 10, Road 2',
        'is_default' => 1,
    ];

    $this->actingAs($owner)->post('/addresses', $addressData)->assertRedirect();
    $address = Address::query()->sole();
    expect($address->is_default)->toBeTrue();

    $mismatchedDistrict = District::query()->where('division_id', '!=', $district->division_id)->firstOrFail();
    $this->from('/dashboard')->post('/addresses', [...$addressData, 'district_id' => $mismatchedDistrict->id])
        ->assertRedirect('/dashboard')->assertSessionHasErrors('district_id');

    $this->actingAs($other)->get('/dashboard')->assertOk()->assertDontSee($order->order_number);
    $this->delete("/addresses/{$address->id}")->assertNotFound();
    $this->assertDatabaseHas('addresses', ['id' => $address->id, 'user_id' => $owner->id]);
});

it('accepts delivered-purchase reviews and exchange requests only within the policy window', function (): void {
    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $order = createPhaseSixOrder($buyer);
    $item = $order->items()->sole();
    markPhaseSixOrderDelivered($order);

    $this->post("/order-items/{$item->id}/review", [
        'rating' => 5,
        'title' => 'দারুণ জার্সি',
        'body' => 'কাপড় ও সেলাই ভালো।',
    ])->assertRedirect();

    $this->post("/order-items/{$item->id}/return", [
        'reason' => 'wrong_size',
        'requested_action' => 'exchange',
    ])->assertRedirect();

    expect(Review::query()->sole()->is_approved)->toBeFalse()
        ->and(ReturnRequest::query()->sole()->status)->toBe('pending');

    $review = Review::query()->sole();
    $product = Product::query()->findOrFail($item->product_id);
    $this->get("/products/{$product->slug}")->assertOk()
        ->assertSee('application/ld+json')
        ->assertSee('"@type":"Product"', false)
        ->assertDontSee('কাপড় ও সেলাই ভালো।');
    $review->update(['is_approved' => true]);
    $this->get("/products/{$product->slug}")->assertSee('কাপড় ও সেলাই ভালো।');

    $lateOrder = createPhaseSixOrder($buyer);
    markPhaseSixOrderDelivered($lateOrder, 8);
    $this->post('/order-items/'.$lateOrder->items()->sole()->id.'/return', [
        'reason' => 'wrong_size',
        'requested_action' => 'exchange',
    ])->assertStatus(422);
});

it('publishes CMS content, exposes sitemap URLs, and renders staff-managed shipments', function (): void {
    $page = CmsPage::create([
        'title' => 'আমাদের সম্পর্কে',
        'slug' => 'about',
        'body' => 'Dhakebir Jersey House',
        'is_published' => true,
    ]);
    $post = BlogPost::create([
        'title' => 'নতুন কালেকশন',
        'slug' => 'new-collection',
        'body' => 'এই সপ্তাহের নতুন ডিজাইন।',
        'published_at' => now(),
        'is_published' => true,
    ]);
    Banner::create([
        'title' => 'New season',
        'subtitle' => 'Fresh designs',
        'link_url' => '/products',
        'is_active' => true,
    ]);
    CmsPage::create(['title' => 'Draft', 'slug' => 'draft', 'body' => 'Hidden']);

    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $order = createPhaseSixOrder($buyer);
    Shipment::create([
        'order_id' => $order->id,
        'courier' => 'Steadfast',
        'tracking_number' => 'TRACK123',
        'status' => 'shipped',
    ]);
    $this->get('/pages/'.$page->slug)->assertOk()->assertSee('Dhakebir Jersey House');
    $this->get('/pages/draft')->assertNotFound();
    $this->get('/blog')->assertOk()->assertSee($post->title);
    $this->get('/blog/'.$post->slug)->assertOk()->assertSee('এই সপ্তাহের নতুন ডিজাইন।');
    $this->get('/')->assertOk()->assertSee('New season')->assertSee('Fresh designs');
    $this->seed(CmsPageSeeder::class);
    $this->get('/pages/return-policy')->assertOk()->assertSee('৭ দিনের মধ্যে');
    $this->get('/sitemap.xml')->assertOk()->assertHeader('content-type', 'application/xml; charset=UTF-8')
        ->assertSee('/pages/about')->assertSee('/blog/new-collection')->assertDontSee('/pages/draft');

    $admin = User::factory()->create();
    $admin->assignRole('admin');
    $this->actingAs($admin);
    foreach (['reviews', 'return-requests', 'shipments', 'cms-pages', 'banners', 'blog-posts', 'store-reports'] as $path) {
        $this->get('/admin/'.$path)->assertOk();
    }
});

it('tracks orders only when both the order number and phone match', function (): void {
    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $order = createPhaseSixOrder($buyer);
    Shipment::create([
        'order_id' => $order->id,
        'courier' => 'Pathao',
        'tracking_number' => 'PATHAO-42',
        'status' => 'in_transit',
    ]);

    $this->post('/track-order', [
        'order_number' => $order->order_number,
        'phone' => '01812345678',
    ])->assertOk()->assertSee($order->order_number)->assertSee('PATHAO-42');

    $this->from('/track-order')->post('/track-order', [
        'order_number' => $order->order_number,
        'phone' => '01712345678',
    ])->assertRedirect('/track-order')->assertSessionHasErrors('order_number');
});

it('sends order status SMS only through a configured gateway', function (): void {
    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $order = createPhaseSixOrder($buyer);
    $order->update(['status' => 'shipped']);

    Http::fake();
    (new SendOrderStatusSms($order->id))->handle();
    Http::assertNothingSent();

    Setting::updateOrCreate(['key' => 'sms_gateway_url'], ['value' => 'https://sms.example.test/send']);
    Setting::updateOrCreate(['key' => 'sms_api_key'], ['value' => 'test-key']);
    Setting::updateOrCreate(['key' => 'sms_sender_id'], ['value' => 'DHAKEBIR']);
    Http::fake(['https://sms.example.test/*' => Http::response(['status' => 'ok'])]);

    (new SendOrderStatusSms($order->id))->handle();
    Http::assertSent(fn (ClientRequest $request): bool => $request->url() === 'https://sms.example.test/send'
        && $request['api_key'] === 'test-key'
        && $request['sender_id'] === 'DHAKEBIR'
        && $request['number'] === '01812345678'
        && str_contains($request['message'], $order->order_number));
});

it('sends branded order status email when the customer provided an email address', function (): void {
    Mail::fake();
    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $order = createPhaseSixOrder($buyer);

    (new SendOrderStatusEmail($order->id))->handle();

    Mail::assertSent(OrderStatusMail::class, fn (OrderStatusMail $mail): bool => $mail->order->order_number === $order->order_number);
    Mail::assertSent(OrderStatusMail::class, fn (OrderStatusMail $mail): bool => str_contains($mail->envelope()->subject, 'Dhakebir Jersey House'));
});

it('renders mobile navigation and compact catalog controls accessibly', function (): void {
    $this->get('/')->assertOk()
        ->assertSee('text-[#5d0d11]', false)
        ->assertSee('aria-controls="mobile-store-sidebar"', false)
        ->assertSee('id="mobile-store-sidebar"', false)
        ->assertSee('mobile-menu-button', false)
        ->assertSee('text-white', false)
        ->assertSee('bg-[#4285f4]', false)
        ->assertSee('nav-cart-button', false)
        ->assertSee('nav-search-button', false)
        ->assertSee('bg-[#771115] px-3 text-sm font-semibold text-white', false)
        ->assertSee('rounded-l-3xl border border-r-0 border-white/70 bg-white/75', false)
        ->assertSee('backdrop-blur-2xl', false)
        ->assertSee('rounded-2xl border border-white/70 bg-white/75', false)
        ->assertSee('backdrop-blur-xl', false)
        ->assertSee('role="search"', false)
        ->assertSee('grid-cols-2', false);

    $this->get('/products')->assertOk()
        ->assertSee('<details class="group">', false)
        ->assertSee('lg:!block', false)
        ->assertSee('grid-cols-2', false);
});
