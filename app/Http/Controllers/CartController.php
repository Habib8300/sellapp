<?php

namespace App\Http\Controllers;

use App\Models\ProductVariant;
use App\Support\CartContents;
use App\Support\CartSession;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CartController extends Controller
{
    public function index(Request $request, CartContents $contents): View
    {
        return view('cart.index', $contents->forRequest($request));
    }

    public function add(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'variant_id' => ['required', 'integer', 'exists:product_variants,id'],
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
            'customization.name' => ['nullable', 'string', 'max:12', 'regex:/^[A-Za-z ]*$/'],
            'customization.enabled' => ['nullable', 'boolean'],
            'customization.number' => ['nullable', 'required_if:customization.enabled,1', 'integer', 'min:0', 'max:99'],
            'customization.font' => ['nullable', 'required_if:customization.enabled,1', 'in:classic,varsity,modern'],
            'customization.color' => ['nullable', 'required_if:customization.enabled,1', 'in:white,black,gold'],
        ]);

        $variant = ProductVariant::query()
            ->with('product')
            ->whereKey($data['variant_id'])
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('category', fn ($category) => $category->where('is_active', true)))
            ->firstOrFail();

        $product = $variant->product;
        $customization = [];
        if ($product->customization_enabled && ($data['customization']['enabled'] ?? false)) {
            if (blank($data['customization']['name'] ?? null)) {
                return back()->withErrors(['customization.name' => 'কাস্টমাইজেশনের জন্য নাম লিখুন।']);
            }

            $customization = array_filter([
                'name' => filled(data_get($data, 'customization.name'))
                    ? mb_strtoupper(trim(data_get($data, 'customization.name')))
                    : null,
                'number' => data_get($data, 'customization.number'),
                'font' => data_get($data, 'customization.font'),
                'color' => data_get($data, 'customization.color'),
            ], fn (mixed $value): bool => $value !== null && $value !== '');

            if (array_key_exists('number', $customization)) {
                $customization['number'] = (int) $customization['number'];
            }
        }
        $isPreorderAvailable = $product->is_preorder
            && (! $product->preorder_close_at || $product->preorder_close_at->isToday() || $product->preorder_close_at->isFuture());

        if (! $isPreorderAvailable && $variant->stock_quantity < 1) {
            return back()->withErrors(['variant_id' => 'এই সাইজ ও রঙটি বর্তমানে মজুত নেই।']);
        }

        $cart = CartSession::lines($request);
        $key = CartSession::key($variant->id, $customization);
        $quantity = collect($cart)
            ->where('variant_id', $variant->id)
            ->sum('quantity') + $data['quantity'];

        if (! $isPreorderAvailable && $quantity > $variant->stock_quantity) {
            return back()->withErrors(['quantity' => 'নির্বাচিত পরিমাণের জন্য পর্যাপ্ত স্টক নেই।']);
        }

        $cart[$key] = [
            'variant_id' => $variant->id,
            'quantity' => $quantity,
            'customization' => $customization,
        ];
        CartSession::save($request, $cart);

        return redirect()->route('cart.index')->with('status', 'পণ্যটি কার্টে যোগ হয়েছে।');
    }

    public function update(Request $request, string $line): RedirectResponse
    {
        $data = $request->validate([
            'quantity' => ['required', 'integer', 'min:1', 'max:20'],
        ]);

        $cart = CartSession::lines($request);
        abort_unless(isset($cart[$line]), 404);
        $variant = ProductVariant::query()->findOrFail($cart[$line]['variant_id']);

        $variant->load('product.category');
        $product = $variant->product;
        $isPreorderAvailable = $product->is_preorder
            && (! $product->preorder_close_at || $product->preorder_close_at->isToday() || $product->preorder_close_at->isFuture());

        if (! $variant->is_active || ! $product->is_active || ! $product->category->is_active || (! $isPreorderAvailable && $data['quantity'] > $variant->stock_quantity)) {
            return back()->withErrors(['quantity' => 'এই পরিমাণের জন্য পর্যাপ্ত স্টক নেই।']);
        }

        $cart[$line]['quantity'] = $data['quantity'];
        CartSession::save($request, $cart);

        return back()->with('status', 'কার্ট আপডেট হয়েছে।');
    }

    public function remove(Request $request, string $line): RedirectResponse
    {
        $cart = CartSession::lines($request);
        abort_unless(isset($cart[$line]), 404);
        unset($cart[$line]);
        CartSession::save($request, $cart);

        return back()->with('status', 'পণ্যটি কার্ট থেকে সরানো হয়েছে।');
    }
}
