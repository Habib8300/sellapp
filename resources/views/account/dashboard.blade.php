@extends('layouts.store')

@section('title', 'আমার অ্যাকাউন্ট')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-6 sm:px-6 sm:py-10">
        <h1 class="text-3xl font-bold">আমার অ্যাকাউন্ট</h1>
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                <ul class="list-inside list-disc text-sm">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <div class="mt-7 grid gap-8 lg:grid-cols-[1fr_23rem]">
            <div class="space-y-8">
                <section>
                    <h2 class="text-xl font-bold">আমার অর্ডার</h2>
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
                                <div class="mt-4 flex flex-wrap gap-3 text-sm">
                                    <a class="font-semibold text-[#771115] hover:underline" href="{{ route('orders.invoice', ['orderNumber' => $order->order_number], absolute: false) }}">ইনভয়েস</a>
                                    @if ($order->shipment?->tracking_url)<a class="font-semibold text-blue-700 hover:underline" href="{{ $order->shipment->tracking_url }}" target="_blank" rel="noopener">কুরিয়ার ট্র্যাক</a>@endif
                                </div>
                            </article>
                        @empty
                            <p class="rounded-xl bg-white p-5 text-gray-500">আপনার কোনো অর্ডার নেই।</p>
                        @endforelse
                    </div>
                    <div class="mt-4">{{ $orders->links() }}</div>
                </section>

                @if ($reviewableItems->isNotEmpty())
                    <section>
                        <h2 class="text-xl font-bold">রিভিউ দিন</h2>
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
                    </section>
                @endif

                @if ($returns->isNotEmpty())
                    <section>
                        <h2 class="text-xl font-bold">Exchange / Return অনুরোধ</h2>
                        <div class="mt-3 space-y-2">@foreach ($returns as $return)<p class="rounded-lg bg-white p-3 text-sm">{{ $return->order->order_number }} · {{ $return->orderItem->product_name }} · {{ ucfirst($return->requested_action) }} · {{ ucfirst($return->status) }}</p>@endforeach</div>
                    </section>
                @endif
            </div>

            <aside>
                <h2 class="text-xl font-bold">সংরক্ষিত ঠিকানা</h2>
                <div class="mt-4 space-y-3">
                    @forelse ($addresses as $address)
                        <article class="rounded-xl border border-gray-200 bg-white p-4">
                            <div class="flex justify-between gap-3"><strong>{{ $address->label }} @if ($address->is_default)<span class="text-xs text-green-700">(Default)</span>@endif</strong>
                                <form method="POST" action="{{ route('addresses.destroy', ['address' => $address->id], absolute: false) }}">@csrf @method('DELETE')<button class="min-h-11 px-2 text-sm text-red-700">মুছুন</button></form>
                            </div>
                            <p class="mt-1 text-sm text-gray-700">{{ $address->recipient_name }} · {{ $address->phone }}</p>
                            <p class="text-sm text-gray-600">{{ $address->address }}, {{ $address->upazila?->name_bn ?: $address->upazila?->name }}, {{ $address->district?->name_bn ?: $address->district?->name }}, {{ $address->division?->name_bn ?: $address->division?->name }}</p>
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
                    <p x-show="locationError" x-text="locationError" role="alert" class="text-sm text-red-700"></p>
                    <textarea name="address" required maxlength="500" rows="2" placeholder="বিস্তারিত ঠিকানা" class="w-full rounded-lg border-gray-300 text-sm">{{ old('address') }}</textarea>
                    <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_default" value="1"> ডিফল্ট ঠিকানা</label>
                    <button class="min-h-11 w-full rounded-lg bg-[#771115] px-4 py-2 font-semibold text-white">ঠিকানা সংরক্ষণ</button>
                </form>
            </aside>
        </div>
    </section>
    <script>
        function addressForm() {
            return {
                division: @js(old('division_id', '')),
                district: '',
                upazila: '',
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
                    const oldDistrict = @js(old('district_id', ''));
                    const oldUpazila = @js(old('upazila_id', ''));
                    if (this.division) {
                        await this.loadDistricts(oldDistrict);
                        if (this.district) await this.loadUpazilas(oldUpazila);
                    }
                },
            };
        }
    </script>
@endsection
