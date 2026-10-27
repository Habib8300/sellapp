@extends('layouts.store')

@section('title', 'ক্যাম্পাস জার্সি')

@section('content')
    @php($heroProduct = $products->first())
    @if ($heroSlides->isNotEmpty())
        <section
            class="home-hero"
            aria-label="স্বাগতম"
            x-data="{ activeSlide: 0, slideCount: {{ $heroSlides->count() }} }"
            @keydown.arrow-right.prevent="activeSlide = (activeSlide + 1) % slideCount"
            @keydown.arrow-left.prevent="activeSlide = (activeSlide - 1 + slideCount) % slideCount"
        >
            @foreach ($heroSlides as $index => $slide)
                <div
                    class="home-hero__slide"
                    @if ($index > 0) x-cloak @endif
                    x-show="activeSlide === {{ $index }}"
                    x-transition.opacity.duration.400ms
                    role="group"
                    aria-roledescription="স্লাইড"
                    aria-label="{{ $index + 1 }} / {{ $heroSlides->count() }}"
                >
                    <div class="home-hero__inner">
                        <div class="home-hero__copy">
                            <p class="home-hero__eyebrow"><span aria-hidden="true"></span> DCU Jersey House</p>
                            <h1>{{ $slide->title }}</h1>
                            @if ($slide->subtitle)
                                <p class="home-hero__description">{{ $slide->subtitle }}</p>
                            @endif
                            <div class="home-hero__actions">
                                <a href="{{ $slide->button_url ?: route('catalog.index', absolute: false) }}" class="home-hero__primary">
                                    {{ $slide->button_text }}
                                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M5 12h14m-6-6 6 6-6 6"/></svg>
                                </a>
                                <a href="#categories" class="home-hero__secondary">ক্যাটাগরি দেখুন</a>
                            </div>
                            <div class="home-hero__highlights" aria-label="জার্সির বৈশিষ্ট্য">
                                <span><b aria-hidden="true">✓</b> ক্যাম্পাস-অনুপ্রাণিত ডিজাইন</span>
                                <span><b aria-hidden="true">✓</b> নিজের সাইজ বেছে নিন</span>
                                <span><b aria-hidden="true">✓</b> সহজ অনলাইন অর্ডার</span>
                            </div>
                        </div>

                        <div class="home-hero__visual" aria-label="ক্যাম্পাস জার্সি">
                            <div class="home-hero__orbit home-hero__orbit--one"></div>
                            <div class="home-hero__orbit home-hero__orbit--two"></div>
                            <div class="home-hero__product">
                                @if ($slide->image_path)
                                    <img src="{{ asset('storage/'.ltrim($slide->image_path, '/')) }}" alt="{{ $slide->title }}" @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif>
                                @elseif ($heroProduct?->images->isNotEmpty())
                                    <img src="{{ asset('storage/'.ltrim($heroProduct->images->first()->image_path, '/')) }}" alt="{{ $heroProduct->images->first()->alt_text ?: ($heroProduct->name_bn ?: $heroProduct->name) }}" @if ($index === 0) fetchpriority="high" @else loading="lazy" @endif>
                                @else
                                    <div class="home-hero__placeholder" aria-hidden="true">
                                        <svg viewBox="0 0 180 190"><path d="m55 19 35 13 35-13 34 22-18 33-18-10v103H57V64L39 74 21 41l34-22Z"/><path d="M69 25c2 15 10 23 21 23s19-8 21-23"/></svg>
                                    </div>
                                @endif
                                <span class="home-hero__product-label">ক্যাম্পাস কালেকশন</span>
                            </div>
                            <div class="home-hero__floating home-hero__floating--top"><span aria-hidden="true">✦</span> নিজের দল, নিজের রঙ</div>
                            <div class="home-hero__floating home-hero__floating--bottom">
                                <span class="home-hero__floating-caption">DCU Jersey House</span>
                                <strong>আপনার ক্যাম্পাস, আপনার পরিচয়</strong>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach

            @if ($heroSlides->count() > 1)
                <div class="home-hero__controls" aria-label="Welcome slide controls">
                    <button type="button" class="home-hero__arrow" aria-label="আগের স্লাইড" @click="activeSlide = (activeSlide - 1 + slideCount) % slideCount">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                    </button>
                    <div class="home-hero__dots" aria-label="স্লাইড বেছে নিন">
                        @foreach ($heroSlides as $index => $slide)
                            <button
                                type="button"
                                class="home-hero__dot"
                                :class="{ 'is-active': activeSlide === {{ $index }} }"
                                :aria-current="activeSlide === {{ $index }} ? 'true' : 'false'"
                                aria-label="স্লাইড {{ $index + 1 }}"
                                @click="activeSlide = {{ $index }}"
                            ></button>
                        @endforeach
                    </div>
                    <button type="button" class="home-hero__arrow" aria-label="পরের স্লাইড" @click="activeSlide = (activeSlide + 1) % slideCount">
                        <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                    </button>
                </div>
            @endif
        </section>
    @endif

    @if ($banners->isNotEmpty())
        <section
            class="home-banners"
            aria-label="প্রচারমূলক ব্যানার"
            x-data="{
                activePage: 0,
                visibleCount: window.matchMedia('(max-width: 700px)').matches ? 1 : 2,
                slideCount: {{ $banners->count() }},
                timer: null,
                resizeHandler: null,
                get pageCount() {
                    return Math.ceil(this.slideCount / this.visibleCount);
                },
                goTo(page) {
                    this.activePage = (page + this.pageCount) % this.pageCount;
                    window.clearInterval(this.timer);
                    this.timer = window.setInterval(() => this.goTo(this.activePage + 1), 5000);
                },
                next() {
                    this.goTo(this.activePage + 1);
                },
                previous() {
                    this.goTo(this.activePage - 1);
                },
                init() {
                    this.$nextTick(() => this.$el.classList.add('is-ready'));
                    this.resizeHandler = () => {
                        const visibleCount = window.matchMedia('(max-width: 700px)').matches ? 1 : 2;

                        if (visibleCount !== this.visibleCount) {
                            this.visibleCount = visibleCount;
                            this.activePage = 0;
                        }
                    };
                    window.addEventListener('resize', this.resizeHandler);
                    this.timer = window.setInterval(() => this.next(), 5000);
                },
                destroy() {
                    window.clearInterval(this.timer);
                    window.removeEventListener('resize', this.resizeHandler);
                }
            }"
            aria-roledescription="carousel"
            aria-live="polite"
        >
            <div class="home-banners__grid">
                @foreach ($banners as $banner)
                    <a
                        href="{{ $banner->link_url ?: route('catalog.index', absolute: false) }}"
                        class="home-banner-card"
                        role="group"
                        aria-roledescription="স্লাইড"
                        aria-label="{{ $banner->title }} — {{ $loop->iteration }} / {{ $banners->count() }}"
                        x-show="Math.floor({{ $loop->index }} / visibleCount) === activePage"
                        @if ($loop->index >= 2) x-cloak @endif
                    >
                        @if ($banner->image_path)
                            <img src="{{ asset('storage/'.ltrim($banner->image_path, '/')) }}" alt="" aria-hidden="true" @if ($loop->first) fetchpriority="high" @else loading="lazy" @endif>
                        @else
                            <span class="home-banner-card__fallback" aria-hidden="true">
                                <svg viewBox="0 0 180 190"><path d="m55 19 35 13 35-13 34 22-18 33-18-10v103H57V64L39 74 21 41l34-22Z"/><path d="M69 25c2 15 10 23 21 23s19-8 21-23"/></svg>
                            </span>
                        @endif
                    </a>
                @endforeach
            </div>
            <div class="home-banners__controls" x-cloak x-show="pageCount > 1">
                <button type="button" class="home-banners__arrow" aria-label="আগের ব্যানার" @click="previous()">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m15 18-6-6 6-6"/></svg>
                </button>
                <span class="home-banners__counter" aria-live="polite"><span x-text="activePage + 1"></span> / <span x-text="pageCount"></span></span>
                <button type="button" class="home-banners__arrow" aria-label="পরের ব্যানার" @click="next()">
                    <svg viewBox="0 0 24 24" aria-hidden="true"><path d="m9 18 6-6-6-6"/></svg>
                </button>
            </div>
        </section>
    @endif

    <section id="categories" class="home-categories">
        <div class="home-categories__inner">
            <div class="home-categories__heading">
                <div>
                    <p>আপনার পছন্দ বেছে নিন</p>
                    <h2>ক্যাটাগরি অনুযায়ী জার্সি</h2>
                    <span>আপনার খেলার ধরন আর ক্যাম্পাসের সঙ্গে মানানসই ডিজাইন খুঁজে নিন।</span>
                </div>
                <a href="{{ route('catalog.index', absolute: false) }}">সব ক্যাটাগরি <span aria-hidden="true">→</span></a>
            </div>
            <div class="home-categories__grid">
                @foreach ($categories as $category)
                    <a href="{{ route('catalog.index', ['category' => $category->slug], absolute: false) }}" class="home-category-card">
                        <span class="home-category-card__icon" aria-hidden="true">
                            <svg viewBox="0 0 32 32"><path d="m10 5 6 2 6-2 6 4-3 6-3-2v14H10V13l-3 2-3-6 6-4Z"/><path d="M13 6c.4 2.8 1.4 4.2 3 4.2S18.6 8.8 19 6"/></svg>
                        </span>
                        <span class="home-category-card__content">
                            <span class="home-category-card__index">{{ sprintf('%02d', $loop->iteration) }} <span>COLLECTION</span></span>
                            <strong>{{ $category->name_bn ?: $category->name }}</strong>
                        </span>
                        <span class="home-category-card__arrow" aria-hidden="true">↗</span>
                    </a>
                @endforeach
            </div>
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

    @if ($blogPosts->isNotEmpty())
        <section class="home-blog" aria-labelledby="home-blog-title">
            <div class="home-blog__inner">
                <div class="home-blog__heading">
                    <div>
                        <p>ক্যাম্পাসের গল্প ও আপডেট</p>
                        <h2 id="home-blog-title">আমাদের ব্লগ</h2>
                    </div>
                    <a href="{{ route('blog.index', absolute: false) }}">সব পোস্ট দেখুন <span aria-hidden="true">→</span></a>
                </div>
                <div class="home-blog__grid">
                    @foreach ($blogPosts as $post)
                        <article class="home-blog__card">
                            @if ($post->cover_image)
                                <a class="home-blog__image" href="{{ route('blog.show', ['slug' => $post->slug], absolute: false) }}" tabindex="-1" aria-hidden="true">
                                    <img src="{{ asset('storage/'.$post->cover_image) }}" alt="" loading="lazy">
                                </a>
                            @endif
                            <div class="home-blog__body">
                                <p class="home-blog__date">{{ ($post->published_at ?? $post->created_at)->format('d M Y') }}</p>
                                <h3><a href="{{ route('blog.show', ['slug' => $post->slug], absolute: false) }}">{{ $post->title }}</a></h3>
                                @if ($post->excerpt)
                                    <p class="home-blog__excerpt">{{ $post->excerpt }}</p>
                                @endif
                                <a class="home-blog__read-more" href="{{ route('blog.show', ['slug' => $post->slug], absolute: false) }}">আরও পড়ুন <span aria-hidden="true">→</span></a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
