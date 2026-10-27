@extends('layouts.store')

@section('title', 'চেকআউট')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10">
        <h1 class="text-2xl font-bold sm:text-3xl">চেকআউট</h1>
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-rose-400/30 bg-rose-400/10 p-4 text-rose-200">
                <p class="font-semibold">অর্ডার সম্পন্ন হয়নি। তথ্যগুলো যাচাই করুন।</p>
                <ul class="mt-2 list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="mt-6 grid min-w-0 gap-6 lg:mt-7 lg:grid-cols-[minmax(0,1fr)_22rem] lg:gap-8" x-data="checkoutForm()">
            <form action="{{ route('checkout.store', absolute: false) }}" method="POST" class="min-w-0 space-y-6">
                @csrf
                <section class="rounded-2xl border border-white/10 bg-slate-900 p-5 sm:p-6">
                    <h2 class="text-xl font-bold">যোগাযোগের তথ্য</h2>
                    <div class="mt-4 grid gap-4 sm:grid-cols-2">
                        <div class="sm:col-span-2"><label for="customer-name" class="text-sm text-slate-300">আপনার নাম *</label><input id="customer-name" name="customer_name" value="{{ old('customer_name', $defaultAddress?->recipient_name ?: auth()->user()?->name) }}" required maxlength="150" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"></div>
                        <div><label for="customer-phone" class="text-sm text-slate-300">মোবাইল নম্বর *</label><input id="customer-phone" name="customer_phone" value="{{ old('customer_phone', $defaultAddress?->phone ?: auth()->user()?->phone) }}" required placeholder="01XXXXXXXXX" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"></div>
                        <div><label for="customer-email" class="text-sm text-slate-300">ইমেইল (ঐচ্ছিক)</label><input id="customer-email" type="email" name="customer_email" value="{{ old('customer_email', auth()->user()?->email) }}" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"></div>
                    </div>
                    @if ($defaultAddress)
                        <p class="mt-3 text-sm text-emerald-300">আপনার ডিফল্ট ঠিকানা থেকে তথ্য পূরণ করা হয়েছে। অর্ডার দেওয়ার আগে চাইলে পরিবর্তন করুন।</p>
                    @elseif (auth()->check())
                        <p class="mt-3 text-sm text-slate-400">চেকআউট দ্রুত করতে অ্যাকাউন্টে একটি ডিফল্ট ঠিকানা সংরক্ষণ করুন।</p>
                    @endif
                </section>

                <section class="rounded-2xl border border-white/10 bg-slate-900 p-5 sm:p-6">
                    <h2 class="text-xl font-bold">ডেলিভারি পদ্ধতি</h2>
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-white/10 p-4" :class="fulfillment === 'delivery' ? 'border-amber-400 bg-amber-400/5' : ''">
                            <input type="radio" name="fulfillment_method" value="delivery" x-model="fulfillment" class="mt-1 text-amber-400 focus:ring-amber-400">
                            <span><strong>হোম ডেলিভারি</strong><span class="mt-1 block text-sm text-slate-400">ঢাকার ভিতরে ৳{{ number_format($deliveryInsideDhaka) }}, বাইরে ৳{{ number_format($deliveryOutsideDhaka) }}</span></span>
                        </label>
                        <label class="flex cursor-pointer gap-3 rounded-xl border border-white/10 p-4" :class="fulfillment === 'campus_pickup' ? 'border-amber-400 bg-amber-400/5' : ''">
                            <input type="radio" name="fulfillment_method" value="campus_pickup" x-model="fulfillment" class="mt-1 text-amber-400 focus:ring-amber-400">
                            <span><strong>ক্যাম্পাস পিকআপ</strong><span class="mt-1 block text-sm text-emerald-300">বিনামূল্যে</span></span>
                        </label>
                    </div>

                    <div x-show="fulfillment === 'delivery'" x-cloak class="mt-5 grid gap-4 sm:grid-cols-2">
                        <p x-show="locationError" x-text="locationError" role="alert" class="sm:col-span-2 text-sm text-rose-300"></p>
                        <div><label for="division-id" class="text-sm text-slate-300">বিভাগ *</label><select id="division-id" name="division_id" x-model="division" @change="loadDistricts()" :required="fulfillment === 'delivery'" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"><option value="">বিভাগ নির্বাচন</option>@foreach ($divisions as $division)<option value="{{ $division->id }}" @selected((string) old('division_id', $defaultAddress?->division_id) === (string) $division->id)>{{ $division->name_bn ?: $division->name }}</option>@endforeach</select></div>
                        <div><label for="district-id" class="text-sm text-slate-300">জেলা *</label><select id="district-id" name="district_id" x-model="district" @change="loadUpazilas()" :required="fulfillment === 'delivery'" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"><option value="">আগে বিভাগ নির্বাচন করুন</option><template x-for="item in districts" :key="item.id"><option :value="item.id" x-text="item.name_bn || item.name"></option></template></select></div>
                        <div><label for="upazila-id" class="text-sm text-slate-300">উপজেলা *</label><select id="upazila-id" name="upazila_id" x-model="upazila" :required="fulfillment === 'delivery'" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"><option value="">আগে জেলা নির্বাচন করুন</option><template x-for="item in upazilas" :key="item.id"><option :value="item.id" x-text="item.name_bn || item.name"></option></template></select></div>
                        <div class="sm:col-span-2"><label for="delivery-address" class="text-sm text-slate-300">বিস্তারিত ঠিকানা *</label><textarea id="delivery-address" name="delivery_address" rows="3" maxlength="500" :required="fulfillment === 'delivery'" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">{{ old('delivery_address', trim(implode(', ', array_filter([$defaultAddress?->campus?->name_bn ?: $defaultAddress?->campus?->name, $defaultAddress?->locality, $defaultAddress?->address])))) }}</textarea></div>
                    </div>

                    <div x-show="fulfillment === 'campus_pickup'" x-cloak class="mt-5">
                        <label for="pickup-point" class="text-sm text-slate-300">পিকআপ ক্যাম্পাস / স্থান *</label>
                        <input id="pickup-point" name="pickup_point" value="{{ old('pickup_point', trim(implode(', ', array_filter([$defaultAddress?->campus?->name_bn ?: $defaultAddress?->campus?->name, $defaultAddress?->locality])))) }}" maxlength="180" :required="fulfillment === 'campus_pickup'" placeholder="যেমন: ঢাকা বিশ্ববিদ্যালয়, টিএসসি" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                        <p class="mt-2 text-xs text-slate-400">পিকআপের সময় ও স্থান নিশ্চিত করতে আমাদের টিম আপনার সাথে যোগাযোগ করবে।</p>
                    </div>
                </section>

                <section class="rounded-2xl border border-white/10 bg-slate-900 p-5 sm:p-6">
                    <h2 class="text-xl font-bold">পেমেন্ট</h2>
                    <div class="mt-4">
                        <label for="coupon-code" class="text-sm text-slate-300">কুপন কোড (ঐচ্ছিক)</label>
                        <input id="coupon-code" name="coupon_code" x-model="couponCode" value="{{ old('coupon_code') }}" maxlength="40" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                        @error('coupon_code')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                    </div>
                    @if ($canPayAdvance)
                        <div class="mt-4 rounded-xl border border-amber-400/20 bg-amber-400/5 p-4">
                            <p class="font-semibold">পেমেন্টের পরিমাণ</p>
                            <label class="mt-3 flex cursor-pointer items-start gap-3 text-sm">
                                <input type="radio" name="payment_plan" value="full" x-model="paymentPlan" class="mt-1 text-amber-400 focus:ring-amber-400">
                                <span>সম্পূর্ণ মূল্য এখন পরিশোধ করুন</span>
                            </label>
                            <label class="mt-3 flex cursor-pointer items-start gap-3 text-sm">
                                <input type="radio" name="payment_plan" value="advance" x-model="paymentPlan" class="mt-1 text-amber-400 focus:ring-amber-400">
                                <span>{{ $advancePaymentPercent }}% অগ্রিম — বাকি টাকা ডেলিভারির সময়</span>
                            </label>
                            <p x-show="paymentPlan === 'advance'" class="mt-3 text-amber-200">অগ্রিম পেমেন্টের জন্য bKash, Nagad বা Rocket নির্বাচন করুন; ক্যাশ অন ডেলিভারি অগ্রিমে প্রযোজ্য নয়।</p>
                        </div>
                    @else
                        <input type="hidden" name="payment_plan" value="full">
                    @endif
                    <div class="mt-4 grid gap-3 sm:grid-cols-2">
                        @foreach (['cod' => ['ক্যাশ অন ডেলিভারি', 'পণ্য হাতে পেয়ে পরিশোধ করুন'], 'bkash' => ['bKash', $paymentInstructions['bkash'] ?: 'অর্ডার নিশ্চিত হলে পেমেন্ট নির্দেশনা দেখানো হবে।'], 'nagad' => ['Nagad', $paymentInstructions['nagad'] ?: 'অর্ডার নিশ্চিত হলে পেমেন্ট নির্দেশনা দেখানো হবে।'], 'rocket' => ['Rocket', $paymentInstructions['rocket'] ?: 'অর্ডার নিশ্চিত হলে পেমেন্ট নির্দেশনা দেখানো হবে।']] as $method => [$label, $description])
                            <label class="cursor-pointer rounded-xl border border-white/10 p-4" :class="payment === '{{ $method }}' ? 'border-amber-400 bg-amber-400/5' : ''">
                                <span class="flex items-start gap-3"><input type="radio" name="payment_method" value="{{ $method }}" x-model="payment" :disabled="paymentPlan === 'advance' && '{{ $method }}' === 'cod'" required class="mt-1 text-amber-400 focus:ring-amber-400"><span><strong>{{ $label }}</strong><span class="mt-1 block text-sm text-slate-400">{{ $description }}</span></span></span>
                            </label>
                        @endforeach
                    </div>
                    <div x-show="['bkash', 'nagad', 'rocket'].includes(payment)" x-cloak class="mt-4">
                        <label for="transaction-reference" class="text-sm text-slate-300">ট্রানজেকশন আইডি (ঐচ্ছিক)</label>
                        <input id="transaction-reference" name="transaction_reference" value="{{ old('transaction_reference') }}" maxlength="120" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                        <p class="mt-2 text-sm text-amber-200">পেমেন্ট যাচাই না হওয়া পর্যন্ত স্ট্যাটাস Pending থাকবে। কোনো গেটওয়ে এখনো স্বয়ংক্রিয়ভাবে চার্জ করবে না।</p>
                    </div>
                </section>

                <section class="rounded-2xl border border-white/10 bg-slate-900 p-5 sm:p-6">
                    <label for="order-note" class="text-lg font-bold">অর্ডার নোট (ঐচ্ছিক)</label>
                    <textarea id="order-note" name="order_note" rows="3" maxlength="1000" class="mt-3 w-full rounded-lg border-white/10 bg-slate-950 text-white">{{ old('order_note') }}</textarea>
                </section>
                <button class="min-h-14 w-full rounded-xl bg-amber-400 px-6 py-4 text-lg font-bold text-slate-950 hover:bg-amber-300">অর্ডার নিশ্চিত করুন</button>
            </form>

            <aside class="h-fit rounded-2xl border border-white/10 bg-slate-900 p-4 sm:p-5 lg:sticky lg:top-24">
                <h2 class="text-xl font-bold">অর্ডার সারাংশ</h2>
                <div class="mt-4 space-y-4">
                    @foreach ($items as $item)
                        <div class="flex justify-between gap-3 text-sm">
                            <span>{{ $item['variant']->product->name_bn ?: $item['variant']->product->name }} · {{ $item['variant']->size }} / {{ $item['variant']->color }} × {{ $item['quantity'] }}@if ($item['customization'])<small class="block text-amber-200">{{ $item['customization']['name'] ?? '' }} {{ $item['customization']['number'] ?? '' }}</small>@endif</span>
                            <span class="shrink-0">৳{{ number_format($item['line_total']) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="mt-5 space-y-2 border-t border-white/10 pt-4 text-sm">
                    <div class="flex justify-between"><span class="text-slate-400">সাবটোটাল</span><span>৳{{ number_format($subtotal) }}</span></div>
                    <div class="flex justify-between"><span class="text-slate-400">ডেলিভারি</span><span x-text="fulfillment === 'campus_pickup' ? 'বিনামূল্যে' : (district ? '৳' + money(deliveryFee) : 'জেলা নির্বাচন করুন')"></span></div>
                    @if ($bulkDiscount)
                        <p class="text-sm text-emerald-300">{{ $bulkDiscount->minimum_quantity }}+ পণ্যে {{ rtrim(rtrim((string) $bulkDiscount->discount_percent, '0'), '.') }}% বাল্ক ছাড় প্রযোজ্য হবে।</p>
                    @endif
                    @if ($studentVerified && $studentDiscountPercent > 0)
                        <p class="text-sm text-emerald-300">যাচাইকৃত শিক্ষার্থী ছাড়: {{ $studentDiscountPercent }}% (কুপন ব্যবহার করলে এই ছাড় প্রযোজ্য হবে না)।</p>
                    @elseif (auth()->check())
                        <a href="{{ route('student-verification.create', absolute: false) }}" class="text-sm text-blue-200 hover:underline">শিক্ষার্থী ছাড়ের জন্য যাচাই করুন</a>
                    @endif
                    <div x-show="estimatedDiscount > 0" class="flex justify-between text-sm text-emerald-300"><span>আনুমানিক ছাড়</span><span x-text="'−৳' + money(estimatedDiscount)"></span></div>
                    <div class="flex justify-between border-t border-white/10 pt-3 text-lg font-bold"><span>সর্বমোট</span><span class="text-amber-300" x-text="'৳' + money({{ (float) $subtotal }} + deliveryFee - estimatedDiscount)"></span></div>
                    <p x-show="couponCode.length > 0" class="text-xs text-slate-400">কুপন যাচাইয়ের পর চূড়ান্ত ছাড় ও মোট নিশ্চিত হবে।</p>
                    @if ($canPayAdvance)
                        <div x-show="paymentPlan === 'advance'" class="flex justify-between text-sm text-amber-200">
                            <span>এখন অগ্রিম ({{ $advancePaymentPercent }}%)</span>
                            <span x-text="'৳' + money(({{ (float) $subtotal }} + deliveryFee) * {{ $advancePaymentPercent }} / 100)"></span>
                        </div>
                        <div x-show="paymentPlan === 'advance'" class="flex justify-between text-sm text-slate-400">
                            <span>ডেলিভারিতে বাকি</span>
                            <span x-text="'৳' + money(({{ (float) $subtotal }} + deliveryFee) * (100 - {{ $advancePaymentPercent }}) / 100)"></span>
                        </div>
                    @endif
                </div>
            </aside>
        </div>
    </section>

    <script>
        function checkoutForm() {
            return {
                fulfillment: @js(old('fulfillment_method', 'delivery')),
                payment: @js(old('payment_method', 'cod')),
                paymentPlan: @js(old('payment_plan', 'full')),
                couponCode: @js(old('coupon_code', '')),
                get estimatedDiscount() {
                    let discount = {{ $bulkDiscount ? (float) $bulkDiscount->discount_percent : 0 }} * {{ (float) $subtotal }} / 100;
                    if (@js($studentVerified) && this.couponCode.length === 0) {
                        discount += ({{ $studentDiscountPercent }} * ({{ (float) $subtotal }} - discount)) / 100;
                    }
                    return discount;
                },
                division: @js(old('division_id', $defaultAddress?->division_id ?? '')),
                district: @js(old('district_id', $defaultAddress?->district_id ?? '')),
                upazila: @js(old('upazila_id', $defaultAddress?->upazila_id ?? '')),
                districts: [],
                upazilas: [],
                locationError: '',
                get deliveryFee() {
                    if (this.fulfillment === 'campus_pickup' || !this.district) return 0;
                    return Number(this.district) === Number(@js($dhakaDistrictId)) ? {{ $deliveryInsideDhaka }} : {{ $deliveryOutsideDhaka }};
                },
                money(value) {
                    return new Intl.NumberFormat('en-US', { maximumFractionDigits: 2 }).format(value);
                },
                async loadDistricts(selectedDistrict = '') {
                    this.locationError = '';
                    this.district = '';
                    this.upazila = '';
                    this.upazilas = [];
                    if (!this.division) { this.districts = []; return; }
                    const response = await fetch(`/locations/divisions/${this.division}/districts`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        this.locationError = 'জেলার তালিকা লোড করা যায়নি। আবার চেষ্টা করুন।';
                        this.districts = [];
                        return;
                    }
                    this.districts = await response.json();
                    this.district = selectedDistrict;
                },
                async loadUpazilas(selectedUpazila = '') {
                    this.locationError = '';
                    this.upazila = '';
                    if (!this.district) { this.upazilas = []; return; }
                    const response = await fetch(`/locations/districts/${this.district}/upazilas`, { headers: { Accept: 'application/json' } });
                    if (!response.ok) {
                        this.locationError = 'উপজেলার তালিকা লোড করা যায়নি। আবার চেষ্টা করুন।';
                        this.upazilas = [];
                        return;
                    }
                    this.upazilas = await response.json();
                    this.upazila = selectedUpazila;
                },
                async init() {
                    this.$watch('paymentPlan', (plan) => {
                        if (plan === 'advance' && this.payment === 'cod') this.payment = 'bkash';
                    });
                    if (this.division) {
                        const selectedDistrict = this.district;
                        const selectedUpazila = this.upazila;
                        await this.loadDistricts(selectedDistrict);
                        if (this.district) {
                            await this.loadUpazilas(selectedUpazila);
                        }
                    }
                },
            };
        }
    </script>
@endsection
