<?php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class WishlistController extends Controller
{
    public function index(Request $request): View
    {
        $ids = $request->session()->get('wishlist', []);
        $products = Product::query()
            ->with(['category', 'images', 'variants'])
            ->where('is_active', true)
            ->whereIn('id', $ids)
            ->get();

        return view('wishlist.index', compact('products'));
    }

    public function toggle(Request $request, Product $product): RedirectResponse
    {
        abort_unless($product->is_active && $product->category()->where('is_active', true)->exists(), 404);

        $wishlist = $request->session()->get('wishlist', []);

        if (in_array($product->id, $wishlist)) {
            $wishlist = array_values(array_diff($wishlist, [$product->id]));
            $message = 'উইশলিস্ট থেকে পণ্যটি সরানো হয়েছে।';
        } else {
            $wishlist[] = $product->id;
            $wishlist = array_values(array_unique($wishlist));
            $message = 'পণ্যটি উইশলিস্টে যোগ হয়েছে।';
        }

        $request->session()->put('wishlist', $wishlist);

        return back()->with('status', $message);
    }
}
