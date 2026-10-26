<?php

namespace App\Services;

use App\Jobs\SendOrderStatusEmail;
use App\Models\BulkDiscountSlab;
use App\Models\Campaign;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\ProductVariant;
use App\Models\Setting;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    /**
     * @param  array<string, mixed>  $checkout
     * @param  array<string, array{variant_id: int, quantity: int, customization: array<string, mixed>}>  $cart
     * @return array{order: Order, access_token: string}
     */
    public function handle(array $checkout, array $cart): array
    {
        if ($cart === []) {
            throw ValidationException::withMessages(['cart' => 'আপনার কার্টে কোনো পণ্য নেই।']);
        }

        $accessToken = Str::random(64);

        $order = DB::transaction(function () use ($checkout, $cart, $accessToken): Order {
            $items = [];
            $subtotal = 0.0;

            foreach ($cart as $line) {
                try {
                    $variant = ProductVariant::query()
                        ->with(['product.category'])
                        ->whereKey($line['variant_id'])
                        ->lockForUpdate()
                        ->firstOrFail();
                } catch (ModelNotFoundException) {
                    throw ValidationException::withMessages(['cart' => 'কার্টের একটি পণ্য আর পাওয়া যাচ্ছে না।']);
                }

                $product = $variant->product;
                if (! $variant->is_active || ! $product->is_active || ! $product->category->is_active) {
                    throw ValidationException::withMessages(['cart' => 'কার্টের একটি পণ্য আর অর্ডার করা যাচ্ছে না।']);
                }

                $isPreorder = $product->is_preorder
                    && (! $product->preorder_close_at || $product->preorder_close_at->isToday() || $product->preorder_close_at->isFuture());

                if (! $isPreorder && $line['quantity'] > $variant->stock_quantity) {
                    throw ValidationException::withMessages(['cart' => "{$product->name} পণ্যের নির্বাচিত পরিমাণে পর্যাপ্ত স্টক নেই।"]);
                }

                if ($line['customization'] !== [] && ! $product->customization_enabled) {
                    throw ValidationException::withMessages(['cart' => 'একটি পণ্যের কাস্টমাইজেশন বর্তমানে বন্ধ আছে।']);
                }

                $customizationCharge = $line['customization'] === []
                    ? 0
                    : ((float) $product->customization_price ?: (float) (Setting::query()->where('key', 'customization_print_charge')->value('value') ?: 150));
                $unitPrice = (float) $product->price + (float) $variant->price_adjustment + $customizationCharge;
                $lineTotal = $unitPrice * $line['quantity'];
                $subtotal += $lineTotal;
                $items[] = [
                    'variant' => $variant,
                    'is_preorder' => $isPreorder,
                    'quantity' => $line['quantity'],
                    'unit_price' => $unitPrice,
                    'line_total' => $lineTotal,
                    'customization' => $line['customization'],
                ];
            }

            $deliveryFee = 0.0;
            if ($checkout['fulfillment_method'] === 'delivery') {
                $district = DB::table('districts')->where('id', $checkout['district_id'])->first();
                if (! $district || (int) $district->division_id !== (int) $checkout['division_id']) {
                    throw ValidationException::withMessages(['district_id' => 'সঠিক বিভাগ ও জেলা নির্বাচন করুন।']);
                }

                $upazila = DB::table('upazilas')->where('id', $checkout['upazila_id'])->first();
                if (! $upazila || (int) $upazila->district_id !== (int) $checkout['district_id']) {
                    throw ValidationException::withMessages(['upazila_id' => 'সঠিক উপজেলা নির্বাচন করুন।']);
                }

                $division = DB::table('divisions')->where('id', $checkout['division_id'])->first();
                $deliveryFee = (float) (Setting::query()
                    ->where('key', $district->name === 'Dhaka' ? 'delivery_inside_dhaka' : 'delivery_outside_dhaka')
                    ->value('value') ?: ($district->name === 'Dhaka' ? 70 : 130));
            } else {
                $division = $district = $upazila = null;
            }

            $quantity = collect($items)->sum('quantity');
            $bulkSlab = BulkDiscountSlab::query()
                ->where('is_active', true)
                ->where('minimum_quantity', '<=', $quantity)
                ->orderByDesc('minimum_quantity')
                ->first();
            $discountAmount = $bulkSlab
                ? min($subtotal, round($subtotal * (float) $bulkSlab->discount_percent / 100, 2))
                : 0.0;

            $coupon = null;
            if (filled($checkout['coupon_code'] ?? null)) {
                $coupon = Coupon::query()
                    ->where('code', mb_strtoupper(trim($checkout['coupon_code'])))
                    ->lockForUpdate()
                    ->first();

                if (! $coupon || ! $coupon->is_active || ($coupon->expires_at && $coupon->expires_at->isPast())
                    || ($coupon->usage_limit !== null && $coupon->used_count >= $coupon->usage_limit)
                    || $subtotal < (float) ($coupon->minimum_order ?? 0)) {
                    throw ValidationException::withMessages(['coupon_code' => 'কুপনটি অবৈধ, মেয়াদোত্তীর্ণ বা অর্ডারের শর্ত পূরণ হয়নি।']);
                }

                if ($coupon->student_only && ! (Auth::user()?->student_verified ?? false)) {
                    throw ValidationException::withMessages(['coupon_code' => 'এই কুপনটি শুধু যাচাইকৃত শিক্ষার্থীদের জন্য।']);
                }

                $discountBase = max(0, $subtotal - $discountAmount);
                $couponDiscount = $coupon->type === 'percent'
                    ? round($discountBase * (float) $coupon->amount / 100, 2)
                    : min($discountBase, (float) $coupon->amount);
                $discountAmount += min($discountBase, $couponDiscount);
            } elseif (Auth::user()?->student_verified) {
                $studentPercent = (float) (Setting::query()->where('key', 'student_discount_percent')->value('value') ?: 0);
                $discountBase = max(0, $subtotal - $discountAmount);
                $discountAmount += min($discountBase, round($discountBase * $studentPercent / 100, 2));
            }

            $total = max(0, $subtotal - $discountAmount) + $deliveryFee;
            $campaign = null;
            if (! empty($checkout['campaign_id'])) {
                $campaign = Campaign::query()->whereKey($checkout['campaign_id'])->lockForUpdate()->first();
                if (! $campaign || ! $campaign->is_active || $campaign->deadline->isPast()) {
                    throw ValidationException::withMessages(['campaign' => 'এই ক্যাম্পেইনের অর্ডারের সময়সীমা শেষ হয়েছে।']);
                }
                if ($campaign->product_id && collect($items)->contains(
                    fn (array $item): bool => $item['variant']->product_id !== $campaign->product_id,
                )) {
                    throw ValidationException::withMessages(['campaign' => 'এই ক্যাম্পেইনের নির্দিষ্ট ডিজাইন থেকেই অর্ডার করতে হবে।']);
                }
            }
            $isAdvanceEligible = collect($items)->contains(
                fn (array $item): bool => $item['is_preorder'] || $item['customization'] !== [],
            );
            $advancePayment = ($checkout['payment_plan'] ?? 'full') === 'advance';

            if ($advancePayment && (! $isAdvanceEligible || $checkout['payment_method'] === 'cod')) {
                throw ValidationException::withMessages([
                    'payment_plan' => 'অগ্রিম পেমেন্ট শুধু কাস্টমাইজড বা প্রি-অর্ডার পণ্যের ডিজিটাল পেমেন্টে প্রযোজ্য।',
                ]);
            }

            $advancePercent = (float) (Setting::query()->where('key', 'advance_payment_percent')->value('value') ?: 50);
            $amountDueNow = $advancePayment ? round($total * $advancePercent / 100, 2) : $total;

            $order = Order::create([
                'order_number' => 'DJH-'.now()->format('ymd').'-'.Str::upper(Str::random(8)),
                'user_id' => Auth::id(),
                'coupon_id' => $coupon?->id,
                'campaign_id' => $campaign?->id,
                'guest_access_token_hash' => Hash::make($accessToken),
                'customer_name' => $checkout['customer_name'],
                'customer_email' => $checkout['customer_email'] ?? null,
                'customer_phone' => $checkout['customer_phone'],
                'fulfillment_method' => $checkout['fulfillment_method'],
                'division_id' => $division->id ?? null,
                'district_id' => $district->id ?? null,
                'upazila_id' => $upazila->id ?? null,
                'division_name' => $division->name_bn ?? $division->name ?? null,
                'district_name' => $district->name_bn ?? $district->name ?? null,
                'upazila_name' => $upazila->name_bn ?? $upazila->name ?? null,
                'delivery_address' => $checkout['delivery_address'] ?? null,
                'pickup_point' => $checkout['pickup_point'] ?? null,
                'order_note' => $checkout['order_note'] ?? null,
                'status' => 'pending',
                'payment_method' => $checkout['payment_method'],
                'payment_status' => 'pending',
                'advance_payment' => $advancePayment,
                'amount_due_now' => $amountDueNow,
                'subtotal' => $subtotal,
                'discount_amount' => $discountAmount,
                'delivery_fee' => $deliveryFee,
                'total' => $total,
            ]);

            foreach ($items as $item) {
                $variant = $item['variant'];
                $product = $variant->product;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_variant_id' => $variant->id,
                    'product_name' => $product->name_bn ?: $product->name,
                    'product_sku' => $variant->sku,
                    'size' => $variant->size,
                    'color' => $variant->color,
                    'quantity' => $item['quantity'],
                    'unit_price' => $item['unit_price'],
                    'line_total' => $item['line_total'],
                    'customization' => $item['customization'] === [] ? null : $item['customization'],
                ]);

                if (! $item['is_preorder']) {
                    $variant->update(['stock_quantity' => $variant->stock_quantity - $item['quantity']]);
                }
            }

            $order->payments()->create([
                'provider' => $checkout['payment_method'],
                'status' => 'pending',
                'amount' => $order->amount_due_now,
                'transaction_reference' => $checkout['transaction_reference'] ?? null,
            ]);

            $order->statusHistory()->create([
                'user_id' => Auth::id(),
                'status' => 'pending',
                'note' => 'Order placed by customer.',
            ]);

            if ($coupon) {
                $coupon->increment('used_count');
            }

            if ($campaign) {
                $campaign->campaignOrders()->create([
                    'order_id' => $order->id,
                    'participant_name' => $order->customer_name,
                    'participant_email' => $order->customer_email,
                    'quantity' => $quantity,
                ]);
            }

            return $order;
        }, attempts: 3);

        SendOrderStatusEmail::dispatch($order->id)->afterCommit();

        return ['order' => $order->load(['items', 'payments']), 'access_token' => $accessToken];
    }
}
