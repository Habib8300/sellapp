@extends('layouts.store')

@section('title', 'সব জার্সি')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <p class="text-sm font-semibold text-amber-300">ঢাকেবির জার্সি হাউজ</p>
        <h1 class="mt-2 text-3xl font-bold">সব জার্সি</h1>
        <div class="mt-7 grid gap-8 lg:grid-cols-[16rem_1fr]">
            <form action="{{ route('catalog.index', absolute: false) }}" method="GET" class="h-fit rounded-2xl border border-white/10 bg-slate-900 p-5">
                <details class="group">
                    <summary class="flex min-h-11 cursor-pointer list-none items-center justify-between font-bold [&::-webkit-details-marker]:hidden lg:pointer-events-none lg:min-h-0">
                        <span>ফিল্টার</span>
                        <span class="text-sm text-slate-500 group-open:rotate-180 lg:hidden" aria-hidden="true">⌄</span>
                    </summary>
                    <div class="mt-3 lg:!block">
                <label class="mt-4 block text-sm text-slate-300" for="filter-q">খুঁজুন</label>
                <input id="filter-q" name="q" value="{{ $filters['q'] ?? '' }}" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">

                <label class="mt-4 block text-sm text-slate-300" for="filter-category">ক্যাটাগরি</label>
                <select id="filter-category" name="category" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                    <option value="">সব ক্যাটাগরি</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->slug }}" @selected(($filters['category'] ?? '') === $category->slug)>{{ $category->name_bn ?: $category->name }}</option>
                    @endforeach
                </select>

                <label class="mt-4 block text-sm text-slate-300" for="filter-collection">কালেকশন</label>
                <select id="filter-collection" name="collection" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                    <option value="">সব কালেকশন</option>
                    @foreach ($collections as $collection)
                        <option value="{{ $collection->slug }}" @selected(($filters['collection'] ?? '') === $collection->slug)>{{ $collection->name_bn ?: $collection->name }}</option>
                    @endforeach
                </select>

                <label class="mt-4 block text-sm text-slate-300" for="filter-size">সাইজ</label>
                <select id="filter-size" name="size" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                    <option value="">সব সাইজ</option>
                    @foreach (['XS', 'S', 'M', 'L', 'XL', 'XXL', '3XL', 'Kids'] as $size)
                        <option value="{{ $size }}" @selected(($filters['size'] ?? '') === $size)>{{ $size }}</option>
                    @endforeach
                </select>

                <label class="mt-4 block text-sm text-slate-300" for="filter-color">রঙ</label>
                <input id="filter-color" name="color" value="{{ $filters['color'] ?? '' }}" placeholder="যেমন Black" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">

                <div class="mt-4 grid grid-cols-2 gap-2">
                    <div><label for="min-price" class="text-sm text-slate-300">সর্বনিম্ন ৳</label><input id="min-price" type="number" min="0" name="min_price" value="{{ $filters['min_price'] ?? '' }}" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"></div>
                    <div><label for="max-price" class="text-sm text-slate-300">সর্বোচ্চ ৳</label><input id="max-price" type="number" min="0" name="max_price" value="{{ $filters['max_price'] ?? '' }}" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white"></div>
                </div>

                <label class="mt-4 block text-sm text-slate-300" for="availability">অ্যাভেইলেবিলিটি</label>
                <select id="availability" name="availability" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                    <option value="">সব</option>
                    <option value="in_stock" @selected(($filters['availability'] ?? '') === 'in_stock')>স্টকে আছে</option>
                    <option value="preorder" @selected(($filters['availability'] ?? '') === 'preorder')>প্রি-অর্ডার</option>
                </select>

                <label class="mt-4 block text-sm text-slate-300" for="sort">সাজান</label>
                <select id="sort" name="sort" class="mt-1 w-full rounded-lg border-white/10 bg-slate-950 text-white">
                    <option value="latest" @selected(($filters['sort'] ?? 'latest') === 'latest')>নতুন</option>
                    <option value="price_asc" @selected(($filters['sort'] ?? '') === 'price_asc')>দাম: কম থেকে বেশি</option>
                    <option value="price_desc" @selected(($filters['sort'] ?? '') === 'price_desc')>দাম: বেশি থেকে কম</option>
                </select>
                <button class="mt-5 min-h-11 w-full rounded-lg bg-amber-400 px-4 py-3 font-bold text-slate-950 hover:bg-amber-300">ফলাফল দেখুন</button>
                <a href="{{ route('catalog.index', absolute: false) }}" class="mt-2 flex min-h-11 items-center justify-center text-sm text-slate-400 hover:text-white">সব ফিল্টার মুছুন</a>
                    </div>
                </details>
            </form>

            <div>
                <div class="mb-4 flex items-center justify-between gap-3">
                    <p class="text-sm text-slate-400">{{ $products->total() }}টি পণ্য পাওয়া গেছে</p>
                    @if (collect($filters)->filter()->isNotEmpty())
                        <a href="{{ route('catalog.index', absolute: false) }}" class="shrink-0 text-sm font-semibold text-[#771115] hover:underline">ফিল্টার মুছুন</a>
                    @endif
                </div>
                @if ($products->isNotEmpty())
                    <div class="grid grid-cols-2 gap-3 sm:gap-5 xl:grid-cols-3">
                        @foreach ($products as $product)
                            @include('catalog.partials.product-card', ['product' => $product])
                        @endforeach
                    </div>
                    <div class="mt-8">{{ $products->links() }}</div>
                @else
                    <div class="rounded-2xl border border-white/10 bg-slate-900 p-10 text-center">
                        <p class="text-xl font-semibold">কোনো পণ্য পাওয়া যায়নি</p>
                        <p class="mt-2 text-slate-400">ফিল্টার পরিবর্তন করে আবার চেষ্টা করুন।</p>
                    </div>
                @endif
            </div>
        </div>
    </section>
@endsection
