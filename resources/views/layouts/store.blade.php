<!DOCTYPE html>
<html lang="bn">
<head>
    @php
        $defaultMetaDescription = 'ঢাকেবির জার্সি হাউজ — বিশ্ববিদ্যালয়ের জার্সি, ক্যাম্পাসের গর্ব।';
        $metaDescription = \App\Models\Setting::query()->where('key', 'meta_description')->value('value') ?: $defaultMetaDescription;
        $gaId = \App\Models\Setting::query()->where('key', 'google_analytics_id')->value('value');
        $gtmId = \App\Models\Setting::query()->where('key', 'google_tag_manager_id')->value('value');
        $pixelId = \App\Models\Setting::query()->where('key', 'facebook_pixel_id')->value('value');
        $facebookUrl = \App\Models\Setting::query()->where('key', 'facebook_url')->value('value');
        $instagramUrl = \App\Models\Setting::query()->where('key', 'instagram_url')->value('value');
        $whatsappNumber = \App\Models\Setting::query()->where('key', 'whatsapp_number')->value('value');
        $supportPhone = \App\Models\Setting::query()->where('key', 'support_phone')->value('value');
        $supportEmail = \App\Models\Setting::query()->where('key', 'support_email')->value('value');
        $brandName = \App\Models\Setting::query()->where('key', 'brand_name')->value('value') ?: 'DCU Jersey House';
        $brandNameBn = \App\Models\Setting::query()->where('key', 'brand_name_bn')->value('value') ?: 'ঢাকেবির জার্সি হাউজ';
        $brandTagline = \App\Models\Setting::query()->where('key', 'brand_tagline')->value('value') ?: 'আপনার ক্যাম্পাস, আপনার পরিচয়, আপনার জার্সি।';
        $brandPrimaryColor = \App\Models\Setting::query()->where('key', 'brand_primary_color')->value('value') ?: '#771115';
        $brandPrimaryColor = preg_match('/^#[0-9a-fA-F]{6}$/', $brandPrimaryColor) ? $brandPrimaryColor : '#771115';
    @endphp
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#771115">
    <meta name="description" content="@yield('description', $metaDescription)">
    <meta property="og:title" content="@yield('title', $brandNameBn) | {{ $brandName }}">
    <meta property="og:description" content="@yield('description', $metaDescription)">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', $brandNameBn) | {{ $brandName }}</title>
    @if ($gaId)
        <script async src="https://www.googletagmanager.com/gtag/js?id={{ urlencode($gaId) }}"></script>
        <script>
            window.dataLayer = window.dataLayer || [];
            function gtag(){dataLayer.push(arguments);}
            gtag('js', new Date());
            gtag('config', '{{ $gaId }}');
        </script>
    @endif
    @if ($gtmId)
        <script>
            (function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src='https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);})(window,document,'script','dataLayer','{{ $gtmId }}');
        </script>
    @endif
    @if ($pixelId)
        <script>
            !function(f,b,e,v,n,t,s)
            {if(f.fbq)return;n=f.fbq=function(){n.callMethod?n.callMethod.apply(n,arguments):n.queue.push(arguments)};
             if(!f._fbq)f._fbq=n;n.push=n;n.loaded=!0;n.version='2.0';n.queue=[];t=b.createElement(e);t.async=!0;
             t.src=v;s=b.getElementsByTagName(e)[0];s.parentNode.insertBefore(t,s)}(window, document,'script',
             'https://connect.facebook.net/en_US/fbevents.js');
            fbq('init', '{{ $pixelId }}');
            fbq('track', 'PageView');
        </script>
    @endif
    @yield('structured-data')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-theme min-h-screen bg-gray-100 text-gray-900 antialiased" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false; $nextTick(() => $refs.sidebarTrigger.focus())">
    <header class="sticky top-2 z-30 mx-2 rounded-2xl border border-white/70 bg-white/75 shadow-lg shadow-gray-900/5 backdrop-blur-xl backdrop-saturate-150 sm:top-4 sm:mx-auto sm:max-w-7xl">
        <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-3 sm:px-6 sm:py-4">
            <a href="{{ route('home', absolute: false) }}" aria-label="DCU Jersey House হোম" class="mr-auto min-w-0 text-base font-bold tracking-tight sm:text-lg">
                <span class="text-[#5d0d11]">ঢাকেবির</span> জার্সি হাউজ
            </a>
            <button x-ref="sidebarTrigger" type="button" class="mobile-menu-button inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg text-xl text-[#771115] transition focus-visible:outline-[#771115] sm:hidden" @click.stop="menuOpen = true; $nextTick(() => $refs.sidebarClose.focus())" :aria-expanded="menuOpen.toString()" aria-controls="mobile-store-sidebar" aria-label="সাইডবার মেনু খুলুন">
                <svg x-show="!menuOpen" aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="M4 6h16M4 12h16M4 18h16"/></svg>
                <svg x-show="menuOpen" x-cloak aria-hidden="true" class="h-6 w-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><path d="m18 6-12 12M6 6l12 12"/></svg>
            </button>
            <form role="search" action="{{ route('catalog.index', absolute: false) }}" method="GET" class="hidden sm:flex sm:w-auto sm:max-w-xs">
                    <label class="sr-only" for="store-search">পণ্য খুঁজুন</label>
                    <input id="store-search" name="q" value="{{ request('q') }}" placeholder="জার্সি খুঁজুন..." class="min-w-0 w-full rounded-l-lg border-0 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-2 focus:ring-[#f7941d]">
                    <button class="nav-search-button inline-flex min-h-11 shrink-0 items-center gap-2 rounded-r-lg bg-[#f7941d] px-4 text-sm font-semibold text-white hover:bg-[#e8840c]"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>খুঁজুন</button>
            </form>
            <nav class="hidden items-center gap-4 text-sm sm:flex">
                    <a href="{{ route('catalog.index', absolute: false) }}" class="text-gray-700 hover:text-[#771115]">শপ</a>
                    <a href="{{ route('team-orders.create', absolute: false) }}" class="text-gray-700 hover:text-[#771115]">টিম অর্ডার</a>
                    <a href="{{ route('blog.index', absolute: false) }}" class="text-gray-700 hover:text-[#771115]">ব্লগ</a>
                    <a href="{{ route('wishlist.index', absolute: false) }}" class="text-gray-700 hover:text-[#771115]">উইশলিস্ট ({{ count(session('wishlist', [])) }})</a>
                    @guest
                        <a href="{{ route('login', absolute: false) }}" class="text-gray-700 hover:text-[#771115]">লগ ইন</a>
                        <a href="{{ route('register', absolute: false) }}" class="rounded-lg bg-[#f7941d] px-3 py-2 font-semibold text-gray-950 hover:bg-[#e8840c]">অ্যাকাউন্ট খুলুন</a>
                    @else
                        <a href="{{ route('dashboard', absolute: false) }}" class="text-gray-700 hover:text-[#771115]">আমার অ্যাকাউন্ট</a>
                    @endguest
            </nav>
            <a href="{{ route('cart.index', absolute: false) }}" aria-label="কার্ট, {{ count(session('cart', [])) }}টি পণ্য" class="nav-cart-button hidden min-h-11 shrink-0 items-center rounded-lg bg-[#771115] px-3 text-sm font-semibold text-white hover:bg-[#5d0d11] sm:inline-flex">
                <svg aria-hidden="true" class="mr-2 h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l2.2 11.1a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>
                কার্ট <span class="ml-1 rounded-full bg-white/20 px-2 py-0.5 text-xs text-white">{{ count(session('cart', [])) }}</span>
            </a>
        </div>
    </header>

    <div x-cloak x-show="menuOpen" x-transition.opacity class="fixed inset-0 z-40 bg-gray-950/25 backdrop-blur-sm sm:hidden" @click="menuOpen = false; $nextTick(() => $refs.sidebarTrigger.focus())" aria-hidden="true"></div>
    <aside id="mobile-store-sidebar" x-cloak x-show="menuOpen" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="translate-x-0" x-transition:leave-end="translate-x-full" @click.outside="menuOpen = false; $nextTick(() => $refs.sidebarTrigger.focus())" class="fixed inset-y-0 right-0 z-50 flex w-[min(88vw,22rem)] flex-col overflow-y-auto rounded-l-3xl border border-r-0 border-white/70 bg-white/75 p-5 shadow-2xl shadow-gray-900/15 backdrop-blur-2xl backdrop-saturate-150 sm:hidden" role="dialog" aria-modal="true" aria-labelledby="mobile-sidebar-title">
        <div class="flex items-center justify-between border-b border-gray-200 pb-4">
            <h2 id="mobile-sidebar-title" class="text-lg font-bold">ঢাকেবির জার্সি হাউজ</h2>
            <button x-ref="sidebarClose" type="button" class="flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-gray-300 text-2xl" @click="menuOpen = false; $nextTick(() => $refs.sidebarTrigger.focus())" aria-label="সাইডবার বন্ধ করুন">×</button>
        </div>
        <form role="search" action="{{ route('catalog.index', absolute: false) }}" method="GET" class="mt-5 flex">
            <label class="sr-only" for="mobile-store-search">পণ্য খুঁজুন</label>
            <input id="mobile-store-search" name="q" value="{{ request('q') }}" placeholder="জার্সি খুঁজুন..." class="min-w-0 w-full rounded-l-lg border-gray-300 bg-gray-50 text-gray-900 placeholder:text-gray-500 focus:border-[#f7941d] focus:ring-[#f7941d]">
            <button class="nav-search-button inline-flex min-h-11 shrink-0 items-center gap-2 rounded-r-lg bg-[#f7941d] px-4 text-sm font-semibold text-white"><svg aria-hidden="true" class="h-4 w-4" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round"><circle cx="11" cy="11" r="7"/><path d="m20 20-4-4"/></svg>খুঁজুন</button>
        </form>
        <a href="{{ route('cart.index', absolute: false) }}" class="nav-cart-button mt-4 flex min-h-12 items-center justify-between rounded-xl bg-[#771115] px-4 font-semibold text-white" @click="menuOpen = false">
            <span class="inline-flex items-center gap-3"><svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M3 3h2l2.2 11.1a2 2 0 0 0 2 1.6h8.9a2 2 0 0 0 2-1.6L22 7H6"/><circle cx="10" cy="20" r="1"/><circle cx="18" cy="20" r="1"/></svg>আমার কার্ট</span><span class="rounded-full bg-white/20 px-2 py-1 text-sm">{{ count(session('cart', [])) }}টি</span>
        </a>
        <nav class="mt-4 flex flex-col gap-1 text-base" aria-label="মোবাইল নেভিগেশন">
            <a href="{{ route('catalog.index', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v14H4zM8 5v14m8-14v14M4 10h16m-16 4h16"/></svg>সব জার্সি</a>
            <a href="{{ route('team-orders.create', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="3"/><circle cx="17" cy="9" r="2.5"/><path d="M3 19c0-3 2.5-5 6-5s6 2 6 5m1-4c3 0 5 1.5 5 4"/></svg>টিম অর্ডার</a>
            <a href="{{ route('blog.index', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M5 4h14v16H5zM8 8h8m-8 4h8m-8 4h5"/></svg>ব্লগ</a>
            <a href="{{ route('wishlist.index', absolute: false) }}" class="flex min-h-12 items-center justify-between rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><span class="inline-flex items-center gap-3"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M20.8 8.6c0 5.2-8.8 10.2-8.8 10.2S3.2 13.8 3.2 8.6A4.6 4.6 0 0 1 12 6a4.6 4.6 0 0 1 8.8 2.6Z"/></svg>উইশলিস্ট</span><span class="rounded-full bg-gray-100 px-2 py-0.5 text-sm">{{ count(session('wishlist', [])) }}</span></a>
            <a href="{{ route('orders.track', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="11" cy="11" r="7"/><path d="m16.5 16.5 4 4M11 7v4l2.5 1.5"/></svg>অর্ডার ট্র্যাক</a>
            <a href="{{ route('quotes.create', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><path d="M4 5h16v14H4zM8 9h8m-8 4h5"/></svg>বাল্ক অর্ডারের কোট</a>
            @guest
                    <a href="{{ route('login', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M5 20c.5-3.5 3-5 7-5s6.5 1.5 7 5"/></svg>লগ ইন</a>
                    <a href="{{ route('register', absolute: false) }}" class="mt-2 flex min-h-12 items-center justify-center gap-2 rounded-lg bg-[#f7941d] px-3 font-semibold text-gray-950" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="9" cy="8" r="4"/><path d="M2 20c.5-3.5 3-5 7-5m10-1v8m-4-4h8"/></svg>অ্যাকাউন্ট খুলুন</a>
            @else
                    <a href="{{ route('dashboard', absolute: false) }}" class="flex min-h-12 items-center gap-3 rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><svg aria-hidden="true" class="h-5 w-5 text-[#771115]" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"><circle cx="12" cy="8" r="4"/><path d="M5 20c.5-3.5 3-5 7-5s6.5 1.5 7 5"/></svg>আমার অ্যাকাউন্ট</a>
            @endguest
        </nav>
        <div class="mt-auto border-t border-gray-200 pt-4">
            <a href="{{ route('cms.page', ['slug' => 'return-policy'], absolute: false) }}" class="flex min-h-11 items-center text-sm text-gray-600 hover:text-[#771115]" @click="menuOpen = false">রিটার্ন ও এক্সচেঞ্জ নীতি</a>
        </div>
    </aside>

    @if (session('status'))
        <div role="status" class="mx-auto mt-4 max-w-7xl px-4 sm:px-6">
            <p class="rounded-lg border border-green-300 bg-green-50 px-4 py-3 text-green-800">{{ session('status') }}</p>
        </div>
    @endif

    <main>@yield('content')</main>

    <footer class="store-footer" style="--store-brand: {{ $brandPrimaryColor }}">
        <div class="store-footer__inner">
            <div class="store-footer__main">
                <section class="store-footer__identity" aria-label="{{ $brandName }}">
                    <a href="{{ route('home', absolute: false) }}" class="store-footer__brand">
                        <span class="store-footer__brand-mark" aria-hidden="true">DH</span>
                        <span>
                            <strong>{{ $brandNameBn }}</strong>
                            <span class="store-footer__brand-english">{{ $brandName }}</span>
                        </span>
                    </a>
                    <p class="store-footer__tagline">{{ $brandTagline }}</p>
                    <div class="store-footer__socials" aria-label="সোশ্যাল মিডিয়া">
                        @if (!blank($facebookUrl))
                            <a href="{{ $facebookUrl }}" target="_blank" rel="noopener noreferrer">
                                <svg class="store-footer__icon--facebook" viewBox="0 0 24 24" aria-hidden="true"><path d="M13.5 21v-8.2h2.8l.4-3.2h-3.2v-2c0-.9.3-1.6 1.6-1.6h1.7V3.1c-.3 0-1.3-.1-2.5-.1-2.5 0-4.2 1.5-4.2 4.3v2.3H7.3v3.2h2.8V21h3.4Z"/></svg>
                                Facebook
                            </a>
                        @endif
                        @if (!blank($instagramUrl))
                            <a href="{{ $instagramUrl }}" target="_blank" rel="noopener noreferrer">
                                <svg class="store-footer__icon--instagram" viewBox="0 0 24 24" aria-hidden="true"><rect x="3.5" y="3.5" width="17" height="17" rx="5"/><circle cx="12" cy="12" r="4"/><circle class="store-footer__icon-dot" cx="17.5" cy="6.8" r="1"/></svg>
                                Instagram
                            </a>
                        @endif
                        @if (!blank($whatsappNumber))
                            <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}" target="_blank" rel="noopener noreferrer">
                                <svg class="store-footer__icon--whatsapp" viewBox="0 0 24 24" aria-hidden="true"><path d="M20 11.7a8 8 0 0 1-11.8 7L4 20l1.3-4A8 8 0 1 1 20 11.7Z"/><path d="M9 8.5c.2-.4.4-.4.7-.4h.4c.2 0 .3.1.4.4l.7 1.6c.1.2.1.4-.1.6l-.5.6c-.2.2-.2.3 0 .6.5.8 1.2 1.4 2.1 1.8.3.1.4.1.6-.1l.7-.8c.2-.2.4-.2.6-.1l1.5.7c.2.1.3.2.3.4 0 .3-.2 1-.7 1.4-.5.4-1.1.6-1.8.4-1.1-.3-2.3-.8-3.5-1.9-1-.9-1.7-2.1-1.9-3.1-.2-.9.2-1.6.5-2.1Z"/></svg>
                                WhatsApp
                            </a>
                        @endif
                    </div>
                </section>

                <nav class="store-footer__links" aria-label="ফুটার নেভিগেশন">
                    <h2>দ্রুত লিংক</h2>
                    <a href="{{ route('cms.page', ['slug' => 'about'], absolute: false) }}">আমাদের সম্পর্কে</a>
                    <a href="{{ route('catalog.index', absolute: false) }}">সব জার্সি দেখুন</a>
                    <a href="{{ route('quotes.create', absolute: false) }}">৫০+ পিসের কোট</a>
                    <a href="{{ route('cms.page', ['slug' => 'return-policy'], absolute: false) }}">রিটার্ন ও এক্সচেঞ্জ নীতি</a>
                    <a href="{{ route('orders.track', absolute: false) }}">অর্ডার ট্র্যাক</a>
                </nav>

                <section class="store-footer__contact">
                    <h2>যোগাযোগ করুন</h2>
                    <p>আপনার অর্ডার বা পণ্য নিয়ে সাহায্য লাগবে? আমরা পাশে আছি।</p>
                    @if (!blank($supportPhone))
                        <a href="tel:{{ preg_replace('/[^0-9+]/', '', $supportPhone) }}">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><path d="M7 3.8 4.8 5c-.9.5-1.3 1.5-1 2.5 1.7 5.9 6.3 10.5 12.2 12.2 1 .3 2-.1 2.5-1l1.2-2.2-4.2-2.1-1.5 1.8a14.7 14.7 0 0 1-5.2-5.2l1.8-1.5L8.5 5.3 7 3.8Z"/></svg>
                            {{ $supportPhone }}
                        </a>
                    @endif
                    @if (!blank($supportEmail))
                        <a href="mailto:{{ $supportEmail }}" class="store-footer__email">
                            <svg viewBox="0 0 24 24" aria-hidden="true"><rect x="3" y="5" width="18" height="14" rx="2"/><path d="m4 7 8 6 8-6"/></svg>
                            {{ $supportEmail }}
                        </a>
                    @endif
                </section>
            </div>
            <div class="store-footer__bottom">
                <span>© {{ date('Y') }} {{ $brandNameBn }} · {{ $brandName }}. সর্বস্বত্ব সংরক্ষিত।</span>
                <a href="/admin">অ্যাডমিন</a>
            </div>
        </div>
    </footer>
</body>
</html>
