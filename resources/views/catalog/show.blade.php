@extends('layouts.store')

@section('title', $product->name_bn ?: $product->name)
@section('description', $product->description ?: ($product->name_bn ?: $product->name))

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <nav class="mb-6 text-sm text-slate-400"><a href="{{ route('catalog.index', absolute: false) }}" class="hover:text-white">জার্সি</a> / {{ $product->category->name_bn ?: $product->category->name }}</nav>
        <div class="grid gap-10 lg:grid-cols-2">
            <div x-data="{ activeImage: 0, images: @js($product->images->map(fn ($image) => ['src' => '/storage/'.ltrim($image->image_path, '/'), 'alt' => $image->alt_text ?: $product->name])->values()) }">
                <div class="relative aspect-square overflow-hidden rounded-3xl border border-white/10 bg-gradient-to-br from-slate-800 via-slate-900 to-amber-950">
                    <template x-if="images.length">
                        <img :src="images[activeImage].src" :alt="images[activeImage].alt" class="h-full w-full object-cover">
                    </template>
                    <template x-if="! images.length">
                        <div class="flex h-full items-center justify-center"><span class="text-8xl text-amber-400/80" aria-hidden="true">✦</span></div>
                    </template>
                </div>
                @if ($product->images->count() > 1)
                    <div class="mt-3 flex gap-3 overflow-x-auto">
                        @foreach ($product->images as $index => $image)
                            <button type="button" @click="activeImage = {{ $index }}" aria-label="{{ $image->view }} image" class="h-20 w-20 shrink-0 overflow-hidden rounded-lg border border-white/10">
                                <img src="/storage/{{ ltrim($image->image_path, '/') }}" alt="{{ $image->alt_text ?: $product->name }}" class="h-full w-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            <div>
                <p class="text-sm font-semibold text-amber-300">{{ $product->category->name_bn ?: $product->category->name }}</p>
                <h1 class="mt-2 text-3xl font-black sm:text-4xl">{{ $product->name_bn ?: $product->name }}</h1>
                <p class="mt-2 text-sm text-slate-400">SKU: {{ $product->sku }}</p>
                <div class="mt-5 flex items-baseline gap-3">
                    <span class="text-3xl font-bold text-amber-300">৳{{ number_format((float) $product->price) }}</span>
                    @if ($product->compare_at_price)
                        <span class="text-lg text-slate-500 line-through">৳{{ number_format((float) $product->compare_at_price) }}</span>
                    @endif
                </div>

                @php($stockStatus = $product->stock_status)
                <p class="mt-4 inline-flex rounded-full px-3 py-1 text-sm {{ $stockStatus === 'preorder' ? 'bg-blue-500/15 text-blue-200' : ($stockStatus === 'out_of_stock' ? 'bg-slate-700 text-slate-300' : ($stockStatus === 'low_stock' ? 'bg-amber-400/15 text-amber-200' : 'bg-emerald-400/15 text-emerald-200')) }}">
                    {{ match ($stockStatus) { 'preorder' => 'প্রি-অর্ডার খোলা', 'out_of_stock' => 'স্টক শেষ', 'low_stock' => 'সীমিত স্টক', default => 'স্টকে আছে' } }}
                </p>
                @if ($product->is_limited_edition && $product->variants->sum('stock_quantity') > 0)
                    <p class="mt-2 text-sm text-rose-300">আর মাত্র {{ $product->variants->sum('stock_quantity') }}টি</p>
                @endif
                @if ($product->preorder_estimated_delivery_at && $stockStatus === 'preorder')
                    <p class="mt-2 text-sm text-slate-300">সম্ভাব্য ডেলিভারি: {{ $product->preorder_estimated_delivery_at->format('d M Y') }}</p>
                @endif

                @if ($product->description)
                    <div class="prose prose-invert mt-6 max-w-none text-slate-300">{!! nl2br(e($product->description)) !!}</div>
                @endif
                <div class="mt-5 flex flex-wrap gap-2 text-sm text-slate-300">
                    @if ($product->fabric_type)<span class="rounded-lg bg-white/5 px-3 py-2">{{ $product->fabric_type }}</span>@endif
                    @if ($product->gsm)<span class="rounded-lg bg-white/5 px-3 py-2">{{ $product->gsm }} GSM</span>@endif
                    @if ($product->fit)<span class="rounded-lg bg-white/5 px-3 py-2">{{ $product->fit->getLabel() }} fit</span>@endif
                </div>

                @if ($product->variants->isNotEmpty() && $stockStatus !== 'out_of_stock')
                    <form action="{{ route('cart.add', absolute: false) }}" method="POST" class="mt-7 rounded-2xl border border-white/10 bg-slate-900 p-5">
                        @csrf
                        <label for="variant" class="block text-sm font-semibold">সাইজ ও রঙ নির্বাচন করুন</label>
                        <select id="variant" name="variant_id" required class="mt-2 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                            <option value="">সাইজ — রঙ — স্টক</option>
                            @foreach ($product->variants->where('is_active', true) as $variant)
                                @php($canOrder = $stockStatus === 'preorder' || $variant->stock_quantity > 0)
                                <option value="{{ $variant->id }}" @disabled(! $canOrder)>
                                    {{ $variant->size }} — {{ $variant->color }}{{ $variant->stock_quantity > 0 ? ' ('.$variant->stock_quantity.'টি)' : ($stockStatus === 'preorder' ? ' (প্রি-অর্ডার)' : ' (স্টক শেষ)') }}
                                </option>
                            @endforeach
                        </select>
                        @error('variant_id')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                        <div class="mt-4 flex gap-3">
                            <div class="w-28">
                                <label for="quantity" class="block text-sm text-slate-300">পরিমাণ</label>
                                <input id="quantity" type="number" name="quantity" min="1" max="20" value="1" required class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                            </div>
                            <button class="mt-auto flex-1 rounded-lg bg-amber-400 px-5 py-3 font-bold text-slate-950 hover:bg-amber-300">কার্টে যোগ করুন</button>
                        </div>
                        @error('quantity')<p class="mt-2 text-sm text-rose-300">{{ $message }}</p>@enderror
                        @if ($product->customization_enabled)
                            @php($backImage = $product->images->firstWhere('view', 'back'))
                            <div x-data="{ enabled: false, name: '', number: '', font: 'varsity', color: 'white' }" class="mt-6 border-t border-white/10 pt-5">
                                <label class="flex cursor-pointer items-center gap-3 font-semibold">
                                    <input type="checkbox" name="customization[enabled]" value="1" x-model="enabled" class="rounded border-white/20 bg-slate-950 text-amber-400 focus:ring-amber-400">
                                    নাম ও নম্বর প্রিন্ট করান (+৳{{ number_format((float) ($product->customization_price ?: $customizationPrintCharge)) }}/পিস)
                                </label>
                                <div x-show="enabled" x-cloak class="mt-4 grid gap-4 sm:grid-cols-2">
                                    <div>
                                        <label for="custom-name" class="block text-sm text-slate-300">নাম (সর্বোচ্চ ১২ অক্ষর)</label>
                                        <input id="custom-name" name="customization[name]" maxlength="12" x-model="name" @input="name = name.toUpperCase()" :required="enabled" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 uppercase text-white">
                                        @error('customization.name')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="custom-number" class="block text-sm text-slate-300">নম্বর (০–৯৯)</label>
                                        <input id="custom-number" name="customization[number]" type="number" min="0" max="99" x-model="number" :required="enabled" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                                        @error('customization.number')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                                    </div>
                                    <div>
                                        <label for="custom-font" class="block text-sm text-slate-300">ফন্ট</label>
                                        <select id="custom-font" name="customization[font]" x-model="font" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                                            <option value="varsity">Varsity</option><option value="classic">Classic</option><option value="modern">Modern</option>
                                        </select>
                                    </div>
                                    <div>
                                        <label for="custom-color" class="block text-sm text-slate-300">প্রিন্টের রঙ</label>
                                        <select id="custom-color" name="customization[color]" x-model="color" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                                            <option value="white">সাদা</option><option value="black">কালো</option><option value="gold">সোনালি</option>
                                        </select>
                                    </div>
                                    <div class="relative col-span-full mx-auto flex aspect-[4/3] w-full max-w-sm items-center justify-center overflow-hidden rounded-xl bg-gradient-to-b from-slate-700 to-slate-900">
                                        @if ($backImage)
                                            <img src="/storage/{{ ltrim($backImage->image_path, '/') }}" alt="জার্সির পেছনের দিক" class="absolute inset-0 h-full w-full object-cover">
                                        @else
                                            <div class="absolute inset-x-1/4 top-6 h-4/5 rounded-t-[45%] bg-amber-500/20"></div>
                                        @endif
                                        <div class="relative z-10 flex flex-col items-center gap-1 font-black drop-shadow-lg" :class="{ 'text-white': color === 'white', 'text-slate-950': color === 'black', 'text-amber-300': color === 'gold' }">
                                            <span x-text="name || 'আপনার নাম'" class="text-lg uppercase tracking-widest"></span>
                                            <span x-text="number || '00'" class="text-6xl" :class="{ 'font-serif': font === 'classic', 'italic': font === 'modern' }"></span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </form>
                @else
                    <p class="mt-7 rounded-xl bg-white/5 p-4 text-slate-300">এই পণ্যটি বর্তমানে অর্ডার করা যাচ্ছে না।</p>
                @endif

                <form action="{{ route('wishlist.toggle', ['product' => $product->id], absolute: false) }}" method="POST" class="mt-3">
                    @csrf
                    <button class="w-full rounded-lg border border-white/15 px-5 py-3 font-semibold hover:bg-white/10">♡ উইশলিস্টে যোগ / সরান</button>
                </form>

                @if ($product->sizeCharts->isNotEmpty())
                    <details class="mt-6 rounded-2xl border border-white/10 bg-slate-900 p-5">
                        <summary class="cursor-pointer font-semibold">সাইজ চার্ট ও মাপ নেওয়ার গাইড</summary>
                        <div class="mt-4 overflow-x-auto">
                            <table class="w-full min-w-[34rem] text-left text-sm">
                                <thead class="text-slate-400"><tr><th class="p-2">সাইজ</th><th class="p-2">বুক (ইঞ্চি)</th><th class="p-2">লম্বা (ইঞ্চি)</th><th class="p-2">কাঁধ (ইঞ্চি)</th><th class="p-2">বুক (সেমি)</th><th class="p-2">লম্বা (সেমি)</th></tr></thead>
                                <tbody>
                                    @foreach ($product->sizeCharts as $chart)
                                        <tr class="border-t border-white/10"><td class="p-2 font-semibold">{{ $chart->size }}</td><td class="p-2">{{ $chart->chest_inches }}</td><td class="p-2">{{ $chart->length_inches }}</td><td class="p-2">{{ $chart->shoulder_inches }}</td><td class="p-2">{{ $chart->chest_cm }}</td><td class="p-2">{{ $chart->length_cm }}</td></tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                        @if ($product->size_guide)<p class="mt-4 text-sm text-slate-300">{{ $product->size_guide }}</p>@else<p class="mt-4 text-sm text-slate-300">বুকের সবচেয়ে চওড়া অংশ ও কাঁধের প্রস্থ মেপে চার্টের সঙ্গে মিলিয়ে সাইজ বেছে নিন।</p>@endif
                    </details>
                @endif
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
            <h2 class="text-2xl font-bold">একই ক্যাটাগরির আরও জার্সি</h2>
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($related as $product)
                    @include('catalog.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        </section>
    @endif
@endsection
