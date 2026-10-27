<?php

use App\Jobs\SendOrderStatusEmail;
use App\Jobs\SendOrderStatusSms;
use App\Mail\OrderStatusMail;
use App\Models\Address;
use App\Models\Campus;
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
        'locality' => 'University Campus, Dhanmondi',
        'address' => 'House 10, Road 2',
        'is_default' => 1,
    ];

    $this->actingAs($owner)->post('/addresses', $addressData)->assertRedirect();
    $address = Address::query()->sole();
    expect($address->is_default)->toBeTrue();

    $this->post('/addresses', [...$addressData, 'label' => 'Office', 'is_default' => 0])->assertRedirect();
    $officeAddress = Address::query()->where('label', 'Office')->sole();
    $this->post("/addresses/{$officeAddress->id}/default")->assertRedirect();
    $this->assertDatabaseHas('addresses', ['id' => $address->id, 'is_default' => false]);
    $this->assertDatabaseHas('addresses', ['id' => $officeAddress->id, 'is_default' => true]);
    $this->put("/addresses/{$officeAddress->id}", [...$addressData, 'label' => 'Updated Office'])
        ->assertRedirect();
    $this->assertDatabaseHas('addresses', ['id' => $officeAddress->id, 'label' => 'Updated Office', 'is_default' => true]);

    $campus = Campus::query()->create([
        'division_id' => $district->division_id,
        'district_id' => $district->id,
        'upazila_id' => $upazila->id,
        'name' => 'Dhaka University',
        'name_bn' => 'ঢাকা বিশ্ববিদ্যালয়',
        'slug' => 'dhaka-university',
        'is_active' => true,
    ]);
    $this->put("/addresses/{$officeAddress->id}", [
        ...$addressData,
        'label' => 'Campus',
        'campus_id' => $campus->id,
        'locality' => '',
    ])->assertRedirect();
    $this->assertDatabaseHas('addresses', ['id' => $officeAddress->id, 'campus_id' => $campus->id]);
    $this->get('/dashboard')
        ->assertOk()
        ->assertSee('ঢাকা বিশ্ববিদ্যালয়')
        ->assertSee('University Campus, Dhanmondi');

    $mismatchedDistrict = District::query()->where('division_id', '!=', $district->division_id)->firstOrFail();
    $this->from('/dashboard')->post('/addresses', [...$addressData, 'district_id' => $mismatchedDistrict->id])
        ->assertRedirect('/dashboard')->assertSessionHasErrors('district_id');

    $this->actingAs($other)->get('/dashboard')->assertOk()->assertDontSee($order->order_number);
    $this->delete("/addresses/{$address->id}")->assertNotFound();
    $this->assertDatabaseHas('addresses', ['id' => $address->id, 'user_id' => $owner->id]);
});

it('gathers customer orders, wishlist, profile, student verification, and return actions in the account', function (): void {
    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $product = Product::query()->where('slug', 'campus-football-jersey')->firstOrFail();
    $order = createPhaseSixOrder($buyer);
    markPhaseSixOrderDelivered($order);
    $item = $order->items()->sole();
    $order->statusHistory()->create([
        'status' => 'confirmed',
        'created_at' => now(),
    ]);
    $order->payments()->create([
        'provider' => 'bkash',
        'status' => 'pending',
        'amount' => 100,
        'transaction_reference' => 'TXN-ACCOUNT-1',
    ]);

    $this->actingAs($buyer)
        ->withSession(['wishlist' => [$product->id]])
        ->get('/dashboard')
        ->assertOk()
        ->assertSee($order->order_number)
        ->assertSee($product->name_bn)
        ->assertSee(route('logout', absolute: false), false)
        ->assertSee('লগ আউট')
        ->assertSee(route('profile.edit', absolute: false), false)
        ->assertSee(route('student-verification.create', absolute: false), false)
        ->assertSee(route('orders.track', absolute: false), false)
        ->assertSee(route('returns.store', ['orderItem' => $item->id], absolute: false), false)
        ->assertSee('অর্ডারের অগ্রগতি')
        ->assertSee('Confirmed')
        ->assertSee('TXN-ACCOUNT-1')
        ->assertSee('মোট অর্ডার')
        ->assertSee('চলমান অর্ডার')
        ->assertSee('আবার কিনুন')
        ->assertSee('account-orders', false)
        ->assertSee('account-wishlist', false)
        ->assertSee('account-addresses', false);

    $this->get('/profile')->assertOk()->assertSee(route('logout', absolute: false), false)->assertSee('লগ আউট');
    $this->post('/logout')->assertRedirect('/');
    $this->assertGuest();
});

it('reorders only the signed-in customer’s available purchased variants', function (): void {
    $buyer = User::factory()->create();
    $buyer->assignRole('customer');
    $other = User::factory()->create();
    $other->assignRole('customer');
    $order = createPhaseSixOrder($buyer);

    $this->actingAs($other)->post("/orders/{$order->id}/reorder")->assertNotFound();

    $response = $this->actingAs($buyer)->post("/orders/{$order->id}/reorder");
    $response->assertRedirect('/cart')->assertSessionHas('status');
    $this->get('/cart')->assertOk()->assertSee('ক্যাম্পাস ফুটবল জার্সি');
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
    $homeResponse = $this->get('/')
        ->assertOk()
        ->assertSee('New season')
        ->assertDontSee('Fresh designs')
        ->assertSee('home-banners', false)
        ->assertSee('home-banner-card', false)
        ->assertSee('home-banners__grid', false)
        ->assertSee('window.setInterval(() => this.next(), 5000)', false)
        ->assertSee($post->title)
        ->assertSee('home-blog', false);
    expect(strpos($homeResponse->getContent(), $post->title))
        ->toBeLessThan(strpos($homeResponse->getContent(), '<footer'));
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

it('shows published blog posts immediately when no publish date was set and hides scheduled posts', function (): void {
    $publishedNow = BlogPost::create([
        'title' => 'Fresh campus blog post',
        'slug' => 'fresh-campus-blog-post',
        'body' => 'This post should be visible immediately.',
        'is_published' => true,
    ]);
    $scheduled = BlogPost::create([
        'title' => 'Scheduled campus blog post',
        'slug' => 'scheduled-campus-blog-post',
        'body' => 'This post is scheduled for later.',
        'published_at' => now()->addDay(),
        'is_published' => true,
    ]);

    $blogResponse = $this->get('/blog')
        ->assertOk()
        ->assertSee($publishedNow->title)
        ->assertDontSee($scheduled->title);
    expect($blogResponse->headers->get('Cache-Control'))->not->toContain('max-age=300');
    $this->get('/')
        ->assertOk()
        ->assertSee($publishedNow->title)
        ->assertDontSee($scheduled->title);
    $this->get('/blog/'.$publishedNow->slug)
        ->assertOk()
        ->assertSee($publishedNow->body);
    $this->get('/blog/'.$scheduled->slug)->assertNotFound();

    $this->get('/sitemap.xml')
        ->assertSee('/blog/'.$publishedNow->slug)
        ->assertDontSee('/blog/'.$scheduled->slug);
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
    Mail::assertSent(OrderStatusMail::class, fn (OrderStatusMail $mail): bool => str_contains($mail->envelope()->subject, 'DCU Jersey House'));
});

it('renders mobile navigation and compact catalog controls accessibly', function (): void {
    $this->get('/')->assertOk()
        ->assertSee('text-[#5d0d11]', false)
        ->assertSee('aria-controls="mobile-store-sidebar"', false)
        ->assertSee('id="mobile-store-sidebar"', false)
        ->assertSee('mobile-menu-button', false)
        ->assertSee('@click.stop="menuOpen = true', false)
        ->assertSee('text-[#771115]', false)
        ->assertSee('nav-cart-button', false)
        ->assertSee('nav-search-button', false)
        ->assertSee('bg-[#771115] px-3 text-sm font-semibold text-white', false)
        ->assertSee('rounded-l-3xl border border-r-0 border-white/70 bg-white/75', false)
        ->assertSee('backdrop-blur-2xl', false)
        ->assertSee('মোবাইল নেভিগেশন', false)
        ->assertSee('<svg aria-hidden="true"', false)
        ->assertSee('rounded-2xl border border-white/70 bg-white/75', false)
        ->assertSee('backdrop-blur-xl', false)
        ->assertSee('role="search"', false)
        ->assertSee('grid-cols-2', false);

    $this->get('/products')->assertOk()
        ->assertSee('filter-glass-card', false)
        ->assertSee('name="category"', false)
        ->assertSee('grid-cols-2', false);
});
