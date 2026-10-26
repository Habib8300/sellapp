<?php

namespace App\Support;

use Illuminate\Http\Request;

class CartSession
{
    /**
     * @return array<string, array{variant_id: int, quantity: int, customization: array<string, mixed>}>
     */
    public static function lines(Request $request): array
    {
        $cart = $request->session()->get('cart', []);
        $lines = [];

        foreach ($cart as $key => $value) {
            if (is_numeric($value)) {
                $lines[(string) $key] = [
                    'variant_id' => (int) $key,
                    'quantity' => (int) $value,
                    'customization' => [],
                ];

                continue;
            }

            if (! is_array($value) || ! isset($value['variant_id'], $value['quantity'])) {
                continue;
            }

            $lines[(string) $key] = [
                'variant_id' => (int) $value['variant_id'],
                'quantity' => (int) $value['quantity'],
                'customization' => is_array($value['customization'] ?? null) ? $value['customization'] : [],
            ];
        }

        return $lines;
    }

    /**
     * @param  array<string, array{variant_id: int, quantity: int, customization: array<string, mixed>}>  $lines
     */
    public static function save(Request $request, array $lines): void
    {
        $request->session()->put('cart', $lines);
    }

    /**
     * @param  array<string, mixed>  $customization
     */
    public static function key(int $variantId, array $customization): string
    {
        if ($customization === []) {
            return (string) $variantId;
        }

        return $variantId.'-'.substr(hash('sha256', json_encode($customization, JSON_THROW_ON_ERROR)), 0, 16);
    }
}
