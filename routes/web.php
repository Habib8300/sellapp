<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\CmsController;
use App\Http\Controllers\CustomerAccountController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\PhaseFiveController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\StorefrontController;
use App\Http\Controllers\WishlistController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', [StorefrontController::class, 'home'])->name('home');
Route::get('/health', [CmsController::class, 'health'])->name('health');
Route::get('/robots.txt', [CmsController::class, 'robots'])->name('robots');
Route::get('/sitemap.xml', [CmsController::class, 'sitemap'])->name('sitemap');
Route::get('/pages/{slug}', [CmsController::class, 'page'])->name('cms.page');
Route::get('/blog', [CmsController::class, 'blogIndex'])->name('blog.index');
Route::get('/blog/{slug}', [CmsController::class, 'blogPost'])->name('blog.show');
Route::get('/track-order', [CustomerAccountController::class, 'trackForm'])->name('orders.track');
Route::post('/track-order', [CustomerAccountController::class, 'track'])->middleware('throttle:checkout')->name('orders.track.submit');
Route::get('/products', [StorefrontController::class, 'index'])->name('catalog.index');
Route::get('/products/{slug}', [StorefrontController::class, 'show'])->name('catalog.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart/items', [CartController::class, 'add'])->name('cart.add');
Route::patch('/cart/items/{line}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/items/{line}', [CartController::class, 'remove'])->name('cart.remove');
Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:checkout')->name('checkout.store');
Route::get('/orders/{orderNumber}', [CheckoutController::class, 'show'])->name('orders.show');
Route::get('/orders/{orderNumber}/invoice', [CheckoutController::class, 'invoice'])->name('orders.invoice');

Route::get('/wishlist', [WishlistController::class, 'index'])->name('wishlist.index');
Route::post('/wishlist/{product}', [WishlistController::class, 'toggle'])->name('wishlist.toggle');
Route::get('/locations/divisions/{division}/districts', [LocationController::class, 'districts'])->whereNumber('division')->name('locations.districts');
Route::get('/locations/districts/{district}/upazilas', [LocationController::class, 'upazilas'])->whereNumber('district')->name('locations.upazilas');
Route::get('/team-orders', [PhaseFiveController::class, 'teamForm'])->name('team-orders.create');
Route::post('/team-orders', [PhaseFiveController::class, 'teamOrder'])->middleware('throttle:checkout')->name('team-orders.store');
Route::get('/campaigns/{slug}', [PhaseFiveController::class, 'campaign'])->name('campaigns.show');
Route::get('/bulk-quote', [PhaseFiveController::class, 'quoteForm'])->name('quotes.create');
Route::post('/bulk-quote', [PhaseFiveController::class, 'quote'])->middleware('throttle:checkout')->name('quotes.store');
Route::get('/admin/production-sheet.csv', [PhaseFiveController::class, 'productionSheet'])->middleware('auth')->name('admin.production-sheet');
Route::middleware('auth')->group(function () {
    Route::get('/student-verification', [PhaseFiveController::class, 'studentVerificationForm'])->name('student-verification.create');
    Route::post('/student-verification', [PhaseFiveController::class, 'studentVerification'])->middleware('throttle:checkout')->name('student-verification.store');
});

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [CustomerAccountController::class, 'dashboard'])->name('dashboard');
    Route::post('/addresses', [CustomerAccountController::class, 'storeAddress'])->name('addresses.store');
    Route::delete('/addresses/{address}', [CustomerAccountController::class, 'destroyAddress'])->name('addresses.destroy');
    Route::post('/order-items/{orderItem}/review', [CustomerAccountController::class, 'review'])->middleware('throttle:checkout')->name('reviews.store');
    Route::post('/order-items/{orderItem}/return', [CustomerAccountController::class, 'returnRequest'])->middleware('throttle:checkout')->name('returns.store');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::post('/switch-to-admin', function (Request $request) {
    Auth::guard('web')->logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect('/admin/login');
})->middleware('auth')->name('admin.switch-account');

require __DIR__.'/auth.php';
