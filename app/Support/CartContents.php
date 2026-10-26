<?php

namespace App\Support;

use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class CartContents
{
    /**
     * @return array{items: Collection<int, array<string, mixed>>, subtotal: float}
     */
    public function forRequest(Request $request): array
    {
        $cart = CartSession::lines($request);
        $variants = ProductVariant::query()
            ->with(['product.images', 'product.category'])
            ->whereIn('id', collect($cart)->pluck('variant_id'))
            ->where('is_active', true)
            ->whereHas('product', fn ($query) => $query
                ->where('is_active', true)
                ->whereHas('category', fn ($category) => $category->where('is_active', true)))
            ->get()
            ->keyBy('id');

        $customizationDefault = (float) (Setting::query()->where('key', 'customization_print_charge')->value('value') ?: 150);
        $items = collect($cart)->map(function (array $line) use ($variants, $customizationDefault): ?array {
            $variant = $variants->get($line['variant_id']);

            if (! $variant) {
                return null;
            }

            $customization = $line['customization'];
            $customizationCharge = $customization !== []
                ? ((float) $variant->product->customization_price ?: $customizationDefault)
                : 0;
            $price = (float) $variant->product->price + (float) $variant->price_adjustment + $customizationCharge;

            return [
                'key' => CartSession::key($variant->id, $customization),
                'variant' => $variant,
                'quantity' => $line['quantity'],
                'customization' => $customization,
                'customization_charge' => $customizationCharge,
                'unit_price' => $price,
                'line_total' => $price * $line['quantity'],
            ];
        })->filter()->values();

        return [
            'items' => $items,
            'subtotal' => $items->sum('line_total'),
        ];
    }
}
