@extends('layouts.store')

@section('title', 'ক্যাম্পাস জার্সি')

@section('content')
    <section class="relative overflow-hidden border-b border-white/10">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,_var(--tw-gradient-stops))] from-amber-500/20 via-slate-950 to-slate-950"></div>
        <div class="relative mx-auto grid max-w-7xl gap-8 px-4 py-10 sm:px-6 sm:py-16 lg:grid-cols-[1.2fr_.8fr] lg:items-center lg:gap-10 lg:py-24">
            <div>
                <p class="text-sm font-semibold uppercase tracking-[.22em] text-amber-300">Dhakebir Jersey House</p>
                <h1 class="mt-4 max-w-3xl text-3xl font-black leading-tight sm:mt-5 sm:text-5xl lg:text-6xl">
                    আপনার ক্যাম্পাস।<br>আপনার পরিচয়। <span class="text-amber-400">আপনার জার্সি।</span>
                </h1>
                <p class="mt-4 max-w-xl leading-7 text-slate-300 sm:mt-6 sm:leading-8">বিশ্ববিদ্যালয়ের জন্য আরামদায়ক, মানসম্মত জার্সি—আপনার ব্যাচ ও ক্যাম্পাসের গর্ব সঙ্গে নিয়ে চলুন।</p>
                <div class="mt-6 flex flex-col gap-3 sm:mt-8 sm:flex-row">
                    <a href="{{ route('catalog.index', absolute: false) }}" class="flex min-h-12 items-center justify-center rounded-xl bg-amber-400 px-6 py-3 text-center font-bold text-slate-950 hover:bg-amber-300">জার্সি দেখুন</a>
                    <a href="#categories" class="flex min-h-12 items-center justify-center rounded-xl border border-white/20 px-6 py-3 text-center font-semibold hover:bg-white/10">ক্যাটাগরি দেখুন</a>
                </div>
            </div>
            <div class="rounded-3xl border border-amber-300/20 bg-slate-900/70 p-5 shadow-xl shadow-amber-950/20 sm:p-8">
                <p class="text-sm font-semibold text-amber-300">ক্যাম্পাস কালেকশন</p>
                <p class="mt-3 text-3xl font-bold">নিজের দল, নিজের রঙ</p>
                <p class="mt-3 text-slate-300">ফুটবল, ক্রিকেট, পোলো এবং আরও অনেক ডিজাইন। সাইজ ও রঙ বেছে নিয়ে সহজেই অর্ডার করুন।</p>
                <div class="mt-7 grid grid-cols-3 gap-3 text-center text-sm">
                    <div class="rounded-xl bg-white/5 p-4"><span class="block text-2xl">⚽</span>ফুটবল</div>
                    <div class="rounded-xl bg-white/5 p-4"><span class="block text-2xl">🏏</span>ক্রিকেট</div>
                    <div class="rounded-xl bg-white/5 p-4"><span class="block text-2xl">👕</span>ক্যাম্পাস</div>
                </div>
            </div>
        </div>
    </section>

    @if ($banners->isNotEmpty())
        <section aria-label="Store announcements" class="mx-auto grid max-w-7xl gap-4 px-4 pt-8 sm:px-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach ($banners as $banner)
                <a href="{{ $banner->link_url ?: route('catalog.index', absolute: false) }}" class="group overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    @if ($banner->image_path)
                        <img src="{{ asset('storage/'.$banner->image_path) }}" alt="{{ $banner->title }}" loading="lazy" class="h-48 w-full object-cover transition group-hover:scale-[1.02]">
                    @endif
                    <div class="p-4">
                        <h2 class="font-bold text-[#771115]">{{ $banner->title }}</h2>
                        @if ($banner->subtitle)<p class="mt-1 text-sm text-gray-600">{{ $banner->subtitle }}</p>@endif
                    </div>
                </a>
            @endforeach
        </section>
    @endif

    <section id="categories" class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-sm font-semibold text-amber-300">আপনার পছন্দ বেছে নিন</p><h2 class="mt-2 text-2xl font-bold sm:text-3xl">ক্যাটাগরি</h2></div>
            <a href="{{ route('catalog.index', absolute: false) }}" class="text-sm text-slate-300 hover:text-amber-300">সব দেখুন →</a>
        </div>
        <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
            @foreach ($categories as $category)
                <a href="{{ route('catalog.index', ['category' => $category->slug], absolute: false) }}" class="rounded-xl border border-white/10 bg-white/5 p-4 text-center font-semibold hover:border-amber-400/60 hover:bg-amber-400/10">
                    {{ $category->name_bn ?: $category->name }}
                </a>
            @endforeach
        </div>
    </section>

    @if ($collections->isNotEmpty())
        <section class="mx-auto max-w-7xl px-4 py-5 sm:px-6">
            <div class="flex flex-wrap items-center gap-3">
                <span class="text-sm font-semibold text-slate-400">কালেকশন:</span>
                @foreach ($collections as $collection)
                    <a href="{{ route('catalog.index', ['collection' => $collection->slug], absolute: false) }}" class="rounded-full border border-white/15 px-4 py-2 text-sm hover:border-amber-400 hover:text-amber-300">{{ $collection->name_bn ?: $collection->name }}</a>
                @endforeach
            </div>
        </section>
    @endif

    <section class="mx-auto max-w-7xl px-4 py-12 sm:px-6">
        <div class="flex items-end justify-between gap-4">
            <div><p class="text-sm font-semibold text-amber-300">নতুন ডিজাইন</p><h2 class="mt-2 text-2xl font-bold sm:text-3xl">জনপ্রিয় জার্সি</h2></div>
            <a href="{{ route('catalog.index', absolute: false) }}" class="text-sm text-slate-300 hover:text-amber-300">সব পণ্য →</a>
        </div>
        @if ($products->isNotEmpty())
            <div class="mt-6 grid grid-cols-2 gap-3 sm:grid-cols-2 sm:gap-5 lg:grid-cols-4">
                @foreach ($products as $product)
                    @include('catalog.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @else
            <p class="mt-6 rounded-2xl border border-white/10 bg-white/5 p-8 text-slate-300">নতুন পণ্য শিগগিরই আসছে।</p>
        @endif
    </section>
@endsection
