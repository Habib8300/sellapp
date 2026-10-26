@extends('layouts.store')

@section('title', 'অর্ডার নিশ্চিত')

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-14 sm:px-6">
        <div class="rounded-3xl border border-emerald-400/20 bg-slate-900 p-6 sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-wider text-emerald-300">Dhakebir Jersey House</p>
            <h1 class="mt-3 text-3xl font-black">অর্ডার গ্রহণ করা হয়েছে</h1>
            <p class="mt-3 text-slate-300">আপনার অর্ডার নম্বর <strong class="text-white">{{ $order->order_number }}</strong>। অর্ডারের আপডেটের জন্য এই নম্বরটি সংরক্ষণ করুন।</p>
            <div class="mt-6 space-y-3 rounded-2xl bg-slate-950 p-5">
                @foreach ($order->items as $item)
                    <div class="flex justify-between gap-3 text-sm"><span>{{ $item->product_name }} · {{ $item->size }} / {{ $item->color }} × {{ $item->quantity }}@if ($item->customization)<small class="block text-amber-200">{{ $item->customization['name'] ?? '' }} {{ $item->customization['number'] ?? '' }}</small>@endif</span><span>৳{{ number_format((float) $item->line_total) }}</span></div>
                @endforeach
                <div class="flex justify-between border-t border-white/10 pt-3 text-sm"><span>সাবটোটাল</span><span>৳{{ number_format((float) $order->subtotal) }}</span></div>
                @if ((float) $order->discount_amount > 0)
                    <div class="flex justify-between text-sm text-emerald-300"><span>ছাড়</span><span>−৳{{ number_format((float) $order->discount_amount) }}</span></div>
                @endif
                <div class="flex justify-between text-sm"><span>ডেলিভারি</span><span>{{ $order->delivery_fee > 0 ? '৳'.number_format((float) $order->delivery_fee) : 'বিনামূল্যে / ক্যাম্পাস পিকআপ' }}</span></div>
                <div class="flex justify-between border-t border-white/10 pt-3 font-bold"><span>মোট</span><span class="text-amber-300">৳{{ number_format((float) $order->total) }}</span></div>
                @if ($order->advance_payment)
                    <div class="flex justify-between text-sm"><span>এখন পরিশোধযোগ্য</span><span>৳{{ number_format((float) $order->amount_due_now) }}</span></div>
                    <div class="flex justify-between text-sm"><span>ডেলিভারির সময় বাকি</span><span>৳{{ number_format((float) $order->total - (float) $order->amount_due_now) }}</span></div>
                @endif
                @if ($order->shipment?->tracking_number)
                    <div class="mt-4 rounded-xl border border-blue-200 bg-blue-50 p-4 text-sm text-blue-900">
                        কুরিয়ার: {{ $order->shipment->courier }} · ট্র্যাকিং: {{ $order->shipment->tracking_number }}
                        @if ($order->shipment->tracking_url)<a class="ml-2 underline" href="{{ $order->shipment->tracking_url }}" target="_blank" rel="noopener">ট্র্যাক করুন</a>@endif
                    </div>
                @endif
            </div>
            <div class="mt-5 rounded-xl border border-white/10 p-4 text-sm">
                <p>পেমেন্ট: <strong>{{ strtoupper($order->payment_method) }}</strong> · স্ট্যাটাস: <strong>{{ ucfirst($order->payment_status) }}</strong></p>
                @if ($order->payment_method !== 'cod')
                    <p class="mt-2 text-amber-200">{{ $paymentInstructions ?: 'পেমেন্টের নির্দেশনা নিশ্চিত করতে আমাদের টিম আপনার সাথে যোগাযোগ করবে।' }}</p>
                    @if ($order->payments->first()?->transaction_reference)<p class="mt-2 text-slate-300">আপনার Transaction ID: {{ $order->payments->first()->transaction_reference }}</p>@endif
                    <p class="mt-2 text-slate-400">ম্যানুয়াল যাচাই সম্পন্ন না হওয়া পর্যন্ত পেমেন্টকে পরিশোধিত দেখানো হবে না।</p>
                @endif
                @if ($order->fulfillment_method === 'delivery')
                    <p class="mt-2">ঠিকানা: {{ $order->delivery_address }}, {{ $order->upazila_name }}, {{ $order->district_name }}, {{ $order->division_name }}</p>
                @else
                    <p class="mt-2">ক্যাম্পাস পিকআপ: {{ $order->pickup_point }}</p>
                @endif
            </div>
            <div class="mt-6 flex flex-wrap gap-3">
                <a href="{{ route('orders.invoice', ['orderNumber' => $order->order_number], absolute: false) }}" class="rounded-lg bg-amber-400 px-5 py-3 font-bold text-slate-950">ইনভয়েস PDF ডাউনলোড</a>
                <a href="{{ route('catalog.index', absolute: false) }}" class="rounded-lg border border-white/15 px-5 py-3 font-semibold">আরও জার্সি দেখুন</a>
            </div>
        </div>
    </section>
@endsection
