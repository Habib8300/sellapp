@extends('layouts.store')

@section('title', 'আমার অ্যাকাউন্ট')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10">
        <div class="flex flex-wrap items-start justify-between gap-4">
            <div>
                <h1 class="text-3xl font-bold">আমার অ্যাকাউন্ট</h1>
                <p class="mt-2 text-gray-600">স্বাগতম, {{ $user->name }}। আপনার অর্ডার, ঠিকানা, পছন্দ ও অ্যাকাউন্ট এখান থেকে পরিচালনা করুন।</p>
            </div>
            <form method="POST" action="{{ route('logout', absolute: false) }}">
                @csrf
                <button type="submit" class="min-h-11 rounded-lg border border-[#771115] px-4 py-2 font-semibold text-[#771115] hover:bg-red-50">লগ আউট</button>
            </form>
        </div>
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                <ul class="list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <nav aria-label="অ্যাকাউন্ট বিভাগ" class="mt-5 flex flex-wrap gap-2">
            <a href="#account-orders" class="min-h-11 rounded-lg border border-gray-200 bg-white px-4 py-2 font-semibold text-[#771115]">অর্ডার</a>
            <a href="#account-wishlist" class="min-h-11 rounded-lg border border-gray-200 bg-white px-4 py-2 font-semibold text-[#771115]">উইশলিস্ট</a>
            <a href="#account-reviews" class="min-h-11 rounded-lg border border-gray-200 bg-white px-4 py-2 font-semibold text-[#771115]">রিভিউ</a>
            <a href="#account-returns" class="min-h-11 rounded-lg border border-gray-200 bg-white px-4 py-2 font-semibold text-[#771115]">রিটার্ন</a>
            <a href="#account-addresses" class="min-h-11 rounded-lg border border-gray-200 bg-white px-4 py-2 font-semibold text-[#771115]">ঠিকানা</a>
            <a href="#account-profile" class="min-h-11 rounded-lg border border-gray-200 bg-white px-4 py-2 font-semibold text-[#771115]">প্রোফাইল</a>
        </nav>

        <div class="mt-6 grid grid-cols-2 gap-3 lg:grid-cols-4">
            <a href="#account-orders" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <span class="text-sm text-gray-600">মোট অর্ডার</span>
                <strong class="mt-1 block text-2xl text-[#771115]">{{ $orderCount }}</strong>
            </a>
            <a href="#account-orders" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <span class="text-sm text-gray-600">চলমান অর্ডার</span>
                <strong class="mt-1 block text-2xl text-[#771115]">{{ $activeOrderCount }}</strong>
            </a>
            <a href="#account-returns" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <span class="text-sm text-gray-600">রিটার্ন অনুরোধ</span>
                <strong class="mt-1 block text-2xl text-[#771115]">{{ $returnCount }}</strong>
            </a>
            <a href="#account-wishlist" class="rounded-2xl border border-gray-200 bg-white p-4 shadow-sm">
                <span class="text-sm text-gray-600">উইশলিস্ট</span>
                <strong class="mt-1 block text-2xl text-[#771115]">{{ $wishlistCount }}</strong>
            </a>
        </div>

        <div id="account-profile" class="mt-6 grid gap-4 sm:grid-cols-2">
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div>
                        <h2 class="text-lg font-bold">আমার প্রোফাইল</h2>
                        <p class="mt-1">{{ $user->name }}</p>
                        <p class="text-sm text-gray-600">{{ $user->email ?: $user->phone }}</p>
                    </div>
                    <a href="{{ route('profile.edit', absolute: false) }}" class="min-h-11 rounded-lg border border-[#771115] px-4 py-2 font-semibold text-[#771115]">প্রোফাইল সম্পাদনা</a>
                </div>
            </section>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm">
                <h2 class="text-lg font-bold">শিক্ষার্থী যাচাই</h2>
                @if ($user->student_verified)
                    <p class="mt-1 text-sm text-green-700">আপনার শিক্ষার্থী পরিচয় যাচাই করা হয়েছে।</p>
                @elseif ($user->student_verification_submitted_at)
                    <p class="mt-1 text-sm text-amber-700">আপনার যাচাইয়ের অনুরোধ পর্যালোচনাধীন।</p>
                @else
                    <p class="mt-1 text-sm text-gray-600">শিক্ষার্থী ছাড় পেতে আপনার পরিচয় যাচাই করুন।</p>
                @endif
                @unless ($user->student_verified)
                    <a href="{{ route('student-verification.create', absolute: false) }}" class="mt-3 inline-flex min-h-11 items-center rounded-lg bg-[#771115] px-4 py-2 font-semibold text-white">শিক্ষার্থী যাচাই</a>
                @endunless
            </section>
        </div>

        <div class="mt-7 grid gap-8 lg:grid-cols-[1fr_23rem]">
            <div class="space-y-8">
                <section id="account-orders">
                    <h2 class="text-xl font-bold">আমার অর্ডার</h2>
                    <a href="{{ route('orders.track', absolute: false) }}" class="mt-2 inline-flex min-h-11 items-center font-semibold text-[#771115] hover:underline">অর্ডার নম্বর দিয়ে ট্র্যাক করুন</a>
                    <div class="mt-4 space-y-4">
                        @forelse ($orders as $order)
                            <article class="min-w-0 rounded-2xl border border-gray-200 bg-white p-4 shadow-sm sm:p-5">
                                <div class="flex flex-wrap items-start justify-between gap-3">
                                    <div class="min-w-0"><a class="break-all font-bold text-[#771115] hover:underline" href="{{ route('orders.show', ['orderNumber' => $order->order_number], absolute: false) }}">{{ $order->order_number }}</a><p class="mt-1 text-sm text-gray-500">{{ $order->created_at->format('d M Y') }} · {{ ucfirst(str_replace('_', ' ', $order->status)) }}</p></div>
                                    <strong class="shrink-0">৳{{ number_format((float) $order->total, 2) }}</strong>
                                </div>
                                @if ($order->shipment?->tracking_number)<p class="mt-2 text-sm text-gray-600">Courier: {{ $order->shipment->courier }} · Tracking: {{ $order->shipment->tracking_number }}</p>@endif
                                <ul class="mt-3 space-y-1 text-sm text-gray-700">
                                    @foreach ($order->items as $item)<li>{{ $item->product_name }} · {{ $item->size }} / {{ $item->color }} × {{ $item->quantity }}</li>@endforeach
                                </ul>
                                <div class="mt-4 rounded-xl bg-gray-50 p-4">
                                    <h3 class="text-sm font-bold">অর্ডারের অগ্রগতি</h3>
                                    @if ($order->statusHistory->isNotEmpty())
                                        <ol class="mt-3 space-y-3">
                                            @foreach ($order->statusHistory->sortBy('created_at') as $history)
                                                <li class="flex gap-3 text-sm">
                                                    <span class="mt-1.5 h-2.5 w-2.5 shrink-0 rounded-full bg-[#771115]" aria-hidden="true"></span>
                                                    <span><strong>{{ ucfirst(str_replace('_', ' ', $history->status)) }}</strong><span class="block text-gray-500">{{ $history->created_at?->format('d M Y, h:i A') }}</span></span>
                                                </li>
                                            @endforeach
                                        </ol>
                                    @else
                                        <p class="mt-2 text-sm">{{ ucfirst(str_replace('_', ' ', $order->status)) }} · {{ $order->created_at->format('d M Y') }}</p>
                                    @endif
                                </div>
                                <div class="mt-3 rounded-xl border border-gray-100 p-4">
                                    <h3 class="text-sm font-bold">পেমেন্ট</h3>
                                    <p class="mt-1 text-sm text-gray-700">{{ ucfirst(str_replace('_', ' ', $order->payment_method)) }} · {{ ucfirst(str_replace('_', ' ', $order->payment_status)) }} · ৳{{ number_format((float) $order->total, 2) }}</p>
                                    @if ($order->payments->isNotEmpty())
                                        <ul class="mt-2 space-y-1 text-sm text-gray-600">
                                            @foreach ($order->payments as $payment)
                                                <li>{{ ucfirst($payment->provider) }} · {{ ucfirst($payment->status) }} · ৳{{ number_format((float) $payment->amount, 2) }}@if ($payment->transaction_reference) · {{ $payment->transaction_reference }}@endif</li>
                                            @endforeach
                                        </ul>
                                    @endif
                                </div>
                                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                                    <a class="font-semibold text-[#771115] hover:underline" href="{{ route('orders.invoice', ['orderNumber' => $order->order_number], absolute: false) }}">ইনভয়েস</a>
                                    @if ($order->shipment?->tracking_url)<a class="font-semibold text-blue-700 hover:underline" href="{{ $order->shipment->tracking_url }}" target="_blank" rel="noopener">কুরিয়ার ট্র্যাক</a>@endif
                                    <form method="POST" action="{{ route('orders.reorder', ['order' => $order->id], absolute: false) }}">
                                        @csrf
                                        <button class="font-semibold text-[#771115] hover:underline">আবার কিনুন</button>
                                    </form>
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl bg-white p-5 text-gray-500">আপনার কোনো অর্ডার নেই।</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $orders->links() }}</div>
                </section>

                <section id="account-wishlist">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <h2 class="text-xl font-bold">আমার উইশলিস্ট</h2>
                        <a href="{{ route('wishlist.index', absolute: false) }}" class="font-semibold text-[#771115] hover:underline">উইশলিস্ট দেখুন</a>
                    </div>
                    @if ($wishlistProducts->isNotEmpty())
                        <div class="mt-4 grid grid-cols-2 gap-3 sm:gap-5 lg:grid-cols-3">
                            @foreach ($wishlistProducts as $product)
                                @include('catalog.partials.product-card', ['product' => $product])
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 rounded-xl bg-white p-4 text-sm text-gray-600">আপনার উইশলিস্টে এখনো কোনো পণ্য নেই।</p>
                    @endif
                </section>

                <section id="account-reviews">
                    <h2 class="text-xl font-bold">রিভিউ দিন</h2>
                    @if ($reviewableItems->isNotEmpty())
                        <div class="mt-4 grid gap-4">
                            @foreach ($reviewableItems as $item)
                                <form method="POST" enctype="multipart/form-data" action="{{ route('reviews.store', ['orderItem' => $item->id], absolute: false) }}" class="space-y-3 rounded-2xl border border-gray-200 bg-white p-5">
                                    @csrf
                                    <p class="font-semibold">{{ $item->product_name }} · {{ $item->size }}</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="text-sm">রেটিং <select name="rating" required class="mt-1 w-full rounded-lg border-gray-300"><option value="">নির্বাচন করুন</option>@for ($rating = 5; $rating >= 1; $rating--)<option value="{{ $rating }}">{{ $rating }} / 5</option>@endfor</select></label>
                                        <label class="text-sm">শিরোনাম<input name="title" maxlength="160" class="mt-1 w-full rounded-lg border-gray-300"></label>
                                    </div>
                                    <label class="block text-sm">রিভিউ<textarea name="body" required maxlength="3000" rows="3" class="mt-1 w-full rounded-lg border-gray-300"></textarea></label>
                                    <label class="block text-sm">ছবি (ঐচ্ছিক)<input type="file" name="photo" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full text-sm"></label>
                                    <button class="min-h-11 rounded-lg bg-[#771115] px-4 py-2 font-semibold text-white">রিভিউ জমা দিন</button>
                                </form>
                            @endforeach
                        </div>
                    @else
                        <p class="mt-3 rounded-xl bg-white p-4 text-sm text-gray-600">রিভিউ দেওয়ার জন্য কোনো নতুন ডেলিভার হওয়া পণ্য নেই।</p>
                    @endif
                </section>

                <section id="account-returns">
                    <h2 class="text-xl font-bold">Exchange / Return অনুরোধ</h2>
                    @if ($returnableItems->isNotEmpty())
                        <div class="mt-4 grid gap-4">
                            @foreach ($returnableItems as $item)
                                <form method="POST" action="{{ route('returns.store', ['orderItem' => $item->id], absolute: false) }}" class="space-y-3 rounded-2xl border border-gray-200 bg-white p-5">
                                    @csrf
                                    <p class="font-semibold">{{ $item->order->order_number }} · {{ $item->product_name }} · {{ $item->size }}</p>
                                    <div class="grid gap-3 sm:grid-cols-2">
                                        <label class="text-sm">অনুরোধের ধরন<select name="requested_action" required class="mt-1 w-full rounded-lg border-gray-300"><option value="exchange">সাইজ এক্সচেঞ্জ</option><option value="return">রিটার্ন</option></select></label>
                                        <label class="text-sm">কারণ<select name="reason" required class="mt-1 w-full rounded-lg border-gray-300"><option value="wrong_size">ভুল সাইজ</option><option value="defective">পণ্যে ত্রুটি</option><option value="wrong_item">ভুল পণ্য</option><option value="other">অন্যান্য</option></select></label>
                                    </div>
                                    <label class="block text-sm">বিস্তারিত (ঐচ্ছিক)<textarea name="details" maxlength="2000" rows="2" class="mt-1 w-full rounded-lg border-gray-300"></textarea></label>
                                    <button class="min-h-11 rounded-lg bg-[#771115] px-4 py-2 font-semibold text-white">অনুরোধ জমা দিন</button>
                                </form>
                            @endforeach
                        </div>
                    @endif
                    @if ($returns->isNotEmpty())
                        <div class="mt-3 space-y-2">@foreach ($returns as $return)<p class="rounded-lg bg-white p-3 text-sm">{{ $return->order->order_number }} · {{ $return->orderItem->product_name }} · {{ ucfirst($return->requested_action) }} · {{ ucfirst($return->status) }}</p>@endforeach</div>
                    @elseif ($returnableItems->isEmpty())
                        <p class="mt-3 rounded-xl bg-white p-4 text-sm text-gray-600">বর্তমানে কোনো এক্সচেঞ্জ বা রিটার্ন অনুরোধ নেই। ডেলিভারির ৭ দিনের মধ্যে যোগ্য অর্ডারের জন্য এখানে অনুরোধ করতে পারবেন।</p>
                    @endif
                </section>
            </div>

            <aside>
                <h2 id="account-addresses" class="text-xl font-bold">সংরক্ষিত ঠিকানা</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($addresses as $address)
                        <article class="rounded-xl border border-gray-200 bg-white p-4">
                            <div class="flex justify-between gap-3"><strong>{{ $address->label }} @if ($address->is_default)<span class="text-xs text-green-700">(Default)</span>@endif</strong>
                                <form method="POST" action="{{ route('addresses.destroy', ['address' => $address->id], absolute: false) }}">@csrf @method('DELETE')<button class="min-h-11 px-2 text-sm text-red-700">মুছুন</button></form>
                            </div>
                            <p class="mt-1 text-sm text-gray-700">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                            <p class="text-sm text-gray-600">{{ $address->campus?->name_bn ?: $address->campus?->name }}{{ $address->locality ? ' · '.$address->locality : '' }}{{ $address->campus || $address->locality ? ' · ' : '' }}{{ $address->address }}, {{ $address->upazila?->name_bn ?: $address->upazila?->name }}, {{ $address->district?->name_bn ?: $address->district?->name }}, {{ $address->division?->name_bn ?: $address->division?->name }}</p>
                            <div class="mt-3 flex flex-wrap gap-3">
                                @unless ($address->is_default)
                                    <form method="POST" action="{{ route('addresses.default', ['address' => $address->id], absolute: false) }}">
                                        @csrf
                                        <button class="min-h-11 text-sm font-semibold text-[#771115]">ডিফল্ট করুন</button>
                                    </form>
                                @endunless
                                <details class="text-sm">
                                    <summary class="flex min-h-11 cursor-pointer items-center font-semibold text-[#771115]">ঠিকানা সম্পাদনা</summary>
                                    <form method="POST" action="{{ route('addresses.update', ['address' => $address->id], absolute: false) }}" x-data='addressForm(@js(["division" => $address->division_id, "district" => $address->district_id, "upazila" => $address->upazila_id]))' class="mt-3 space-y-3 rounded-xl bg-gray-50 p-4">
                                        @csrf
                                        @method('PUT')
                                        <input name="label" value="{{ $address->label }}" required maxlength="80" placeholder="ঠিকানার নাম" class="w-full rounded-lg border-gray-300">
                                        <input name="recipient_name" value="{{ $address->recipient_name }}" required maxlength="150" placeholder="প্রাপকের নাম" class="w-full rounded-lg border-gray-300">
                                        <input name="phone" value="{{ $address->phone }}" required placeholder="01XXXXXXXXX" class="w-full rounded-lg border-gray-300">
                                        <select name="division_id" x-model="division" @change="loadDistricts()" required class="w-full rounded-lg border-gray-300"><option value="">বিভাগ নির্বাচন করুন</option>@foreach ($divisions as $division)<option value="{{ $division->id }}">{{ $division->name_bn ?: $division->name }}</option>@endforeach</select>
                                        <select name="district_id" x-model="district" @change="loadUpazilas()" :disabled="!division" required class="w-full rounded-lg border-gray-300"><option value="">জেলা নির্বাচন করুন</option><template x-for="item in districts" :key="item.id"><option :value="item.id" x-text="item.name_bn || item.name"></option></template></select>
                                        <select name="upazila_id" x-model="upazila" :disabled="!district" required class="w-full rounded-lg border-gray-300"><option value="">উপজেলা নির্বাচন করুন</option><template x-for="item in upazilas" :key="item.id"><option :value="item.id" x-text="item.name_bn || item.name"></option></template></select>
                                        <select name="campus_id" class="w-full rounded-lg border-gray-300">
                                            <option value="">তালিকা থেকে ক্যাম্পাস (ঐচ্ছিক)</option>
                                            @foreach ($campuses as $campus)
                                                <option value="{{ $campus->id }}" @selected($address->campus_id === $campus->id)>{{ $campus->name_bn ?: $campus->name }} — {{ $campus->district?->name_bn ?: $campus->district?->name }}</option>
                                            @endforeach
                                        </select>
                                        <input name="locality" value="{{ $address->locality }}" maxlength="180" placeholder="এলাকা / শহর / ক্যাম্পাসের নাম (তালিকায় না থাকলে লিখুন)" class="w-full rounded-lg border-gray-300">
                                        <textarea name="address" required maxlength="500" rows="2" placeholder="বিস্তারিত ঠিকানা" class="w-full rounded-lg border-gray-300">{{ $address->address }}</textarea>
                                        @unless ($address->is_default)
                                            <label class="flex items-center gap-2"><input type="checkbox" name="is_default" value="1"> ডিফল্ট ঠিকানা করুন</label>
                                        @endunless
                                        <button class="min-h-11 rounded-lg bg-[#771115] px-4 font-semibold text-white">পরিবর্তন সংরক্ষণ</button>
                                    </form>
                                </details>
                            </div>
                        </article>
                    @empty
                        <p class="rounded-xl bg-white p-4 text-sm text-gray-500">কোনো ঠিকানা সংরক্ষণ করা নেই।</p>
                    @endforelse
                </div>
                <form method="POST" action="{{ route('addresses.store', absolute: false) }}" x-data="addressForm()" class="mt-4 space-y-3 rounded-2xl border border-gray-200 bg-white p-4">
                    @csrf
                    <h3 class="font-semibold">নতুন ঠিকানা</h3>
                    <input name="label" value="{{ old('label') }}" required maxlength="80" placeholder="ঠিকানার নাম (যেমন বাসা)" class="w-full rounded-lg border-gray-300 text-sm">
                    <input name="recipient_name" value="{{ old('recipient_name') }}" required maxlength="150" placeholder="প্রাপকের নাম" class="w-full rounded-lg border-gray-300 text-sm">
                    <input name="phone" value="{{ old('phone') }}" required placeholder="01XXXXXXXXX" class="w-full rounded-lg border-gray-300 text-sm">
                    <select name="division_id" x-model="division" @change="loadDistricts()" required class="w-full rounded-lg border-gray-300 text-sm"><option value="">বিভাগ নির্বাচন করুন</option>@foreach ($divisions as $division)<option value="{{ $division->id }}">{{ $division->name_bn ?: $division->name }}</option>@endforeach</select>
                    <select name="district_id" x-model="district" @change="loadUpazilas()" :disabled="!division" required class="w-full rounded-lg border-gray-300 text-sm"><option value="">আগে বিভাগ নির্বাচন করুন</option><template x-for="item in districts" :key="item.id"><option :value="item.id" x-text="item.name_bn || item.name"></option></template></select>
                    <select name="upazila_id" x-model="upazila" :disabled="!district" required class="w-full rounded-lg border-gray-300 text-sm"><option value="">আগে জেলা নির্বাচন করুন</option><template x-for="item in upazilas" :key="item.id"><option :value="item.id" x-text="item.name_bn || item.name"></option></template></select>
                    <select name="campus_id" class="w-full rounded-lg border-gray-300 text-sm">
                        <option value="">তালিকা থেকে ক্যাম্পাস (ঐচ্ছিক)</option>
                        @foreach ($campuses as $campus)
                            <option value="{{ $campus->id }}" @selected(old('campus_id') == $campus->id)>{{ $campus->name_bn ?: $campus->name }} — {{ $campus->district?->name_bn ?: $campus->district?->name }}</option>
                        @endforeach
                    </select>
                    <input name="locality" value="{{ old('locality') }}" maxlength="180" placeholder="এলাকা / শহর / ক্যাম্পাসের নাম (তালিকায় না থাকলে লিখুন)" class="w-full rounded-lg border-gray-300 text-sm">
                    <p x-show="locationError" x-text="locationError" role="alert" class="text-sm text-red-700"></p>
                    <textarea name="address" required maxlength="500" rows="2" placeholder="বাড়ি/রোড/বিল্ডিংসহ বিস্তারিত ঠিকানা" class="w-full rounded-lg border-gray-300 text-sm">{{ old('address') }}</textarea>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> ডিফল্ট ঠিকানা</label>
                    <button class="min-h-11 w-full rounded-lg bg-[#771115] px-4 py-2 font-semibold text-white">ঠিকানা সংরক্ষণ</button>
                </form>
            </aside>
        </div>
    </section>
    <script>
        function addressForm(initial = {}) {
            return {
                division: initial.division ?? @js(old('division_id', '')),
                district: initial.district ?? '',
                upazila: initial.upazila ?? '',
                districts: [],
                upazilas: [],
                locationError: '',
                async loadDistricts(selectedDistrict = '') {
                    this.district = '';
                    this.upazila = '';
                    this.upazilas = [];
                    if (!this.division) {
                        this.districts = [];
                        return;
                    }

                    try {
                        const url = @js(route('locations.districts', ['division' => '__id__'], absolute: false)).replace('__id__', this.division);
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (!response.ok) throw new Error();
                        this.districts = await response.json();
                        this.district = selectedDistrict;
                    } catch {
                        this.locationError = 'জেলার তালিকা লোড করা যায়নি। আবার চেষ্টা করুন।';
                    }
                },
                async loadUpazilas(selectedUpazila = '') {
                    this.upazila = '';
                    if (!this.district) {
                        this.upazilas = [];
                        return;
                    }

                    try {
                        const url = @js(route('locations.upazilas', ['district' => '__id__'], absolute: false)).replace('__id__', this.district);
                        const response = await fetch(url, { headers: { Accept: 'application/json' } });
                        if (!response.ok) throw new Error();
                        this.upazilas = await response.json();
                        this.upazila = selectedUpazila;
                    } catch {
                        this.locationError = 'উপজেলার তালিকা লোড করা যায়নি। আবার চেষ্টা করুন।';
                    }
                },
                async init() {
                    const oldDistrict = @js(old('district_id', '')) || this.district;
                    const oldUpazila = @js(old('upazila_id', '')) || this.upazila;
                    if (this.division) {
                        await this.loadDistricts(oldDistrict);
                        if (this.district) await this.loadUpazilas(oldUpazila);
                    }
                },
            };
        }
    </script>
@endsection
