<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#771115">
    <meta name="description" content="@yield('description', 'ঢাকেবির জার্সি হাউজ — বিশ্ববিদ্যালয়ের জার্সি, ক্যাম্পাসের গর্ব।')">
    <meta property="og:title" content="@yield('title', 'ঢাকেবির জার্সি হাউজ') | Dhakebir Jersey House">
    <meta property="og:description" content="@yield('description', 'ঢাকেবির জার্সি হাউজ — বিশ্ববিদ্যালয়ের জার্সি, ক্যাম্পাসের গর্ব।')">
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ url()->current() }}">
    <link rel="canonical" href="{{ url()->current() }}">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <title>@yield('title', 'ঢাকেবির জার্সি হাউজ') | Dhakebir Jersey House</title>
    @yield('structured-data')
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-theme min-h-screen bg-gray-100 text-gray-900 antialiased" x-data="{ menuOpen: false }" @keydown.escape.window="menuOpen = false; $nextTick(() => $refs.sidebarTrigger.focus())">
    <header class="sticky top-2 z-30 mx-2 rounded-2xl border border-white/70 bg-white/75 shadow-lg shadow-gray-900/5 backdrop-blur-xl backdrop-saturate-150 sm:top-4 sm:mx-auto sm:max-w-7xl">
        <div class="mx-auto flex max-w-7xl items-center gap-5 px-4 py-3 sm:px-6 sm:py-4">
            <a href="{{ route('home', absolute: false) }}" aria-label="Dhakebir Jersey House হোম" class="mr-auto min-w-0 text-base font-bold tracking-tight sm:text-lg">
                <span class="text-[#5d0d11]">ঢাকেবির</span> জার্সি হাউজ
            </a>
            <button x-ref="sidebarTrigger" type="button" class="mobile-menu-button inline-flex min-h-11 min-w-11 items-center justify-center rounded-lg border border-[#4285f4] bg-[#4285f4] text-xl text-white shadow-sm transition hover:bg-[#326fd3] focus-visible:outline-[#4285f4] sm:hidden" @click="menuOpen = true; $nextTick(() => $refs.sidebarClose.focus())" :aria-expanded="menuOpen.toString()" aria-controls="mobile-store-sidebar" aria-label="সাইডবার মেনু খুলুন">
                    <span aria-hidden="true">☰</span>
            </button>
            <form role="search" action="{{ route('catalog.index', absolute: false) }}" method="GET" class="hidden sm:flex sm:w-auto sm:max-w-xs">
                    <label class="sr-only" for="store-search">পণ্য খুঁজুন</label>
                    <input id="store-search" name="q" value="{{ request('q') }}" placeholder="জার্সি খুঁজুন..." class="min-w-0 w-full rounded-l-lg border-0 bg-gray-100 px-3 py-2 text-sm text-gray-900 placeholder:text-gray-500 focus:ring-2 focus:ring-[#f7941d]">
                    <button class="nav-search-button min-h-11 shrink-0 rounded-r-lg bg-[#f7941d] px-4 text-sm font-semibold text-white hover:bg-[#e8840c]">খুঁজুন</button>
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
                    কার্ট <span class="ml-1 rounded-full bg-[#771115] px-2 py-0.5 text-xs text-white">{{ count(session('cart', [])) }}</span>
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
            <button class="nav-search-button min-h-11 shrink-0 rounded-r-lg bg-[#f7941d] px-4 text-sm font-semibold text-white">খুঁজুন</button>
        </form>
        <a href="{{ route('cart.index', absolute: false) }}" class="nav-cart-button mt-4 flex min-h-12 items-center justify-between rounded-xl bg-[#771115] px-4 font-semibold text-white" @click="menuOpen = false">
            <span>আমার কার্ট</span><span class="rounded-full bg-white/20 px-2 py-1 text-sm">{{ count(session('cart', [])) }}টি</span>
        </a>
        <nav class="mt-4 flex flex-col gap-1 text-base" aria-label="মোবাইল নেভিগেশন">
            <a href="{{ route('catalog.index', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">সব জার্সি</a>
            <a href="{{ route('team-orders.create', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">টিম অর্ডার</a>
            <a href="{{ route('blog.index', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">ব্লগ</a>
            <a href="{{ route('wishlist.index', absolute: false) }}" class="flex min-h-12 items-center justify-between rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false"><span>উইশলিস্ট</span><span>{{ count(session('wishlist', [])) }}</span></a>
            <a href="{{ route('orders.track', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">অর্ডার ট্র্যাক</a>
            <a href="{{ route('quotes.create', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">বাল্ক অর্ডারের কোট</a>
            @guest
                    <a href="{{ route('login', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">লগ ইন</a>
                    <a href="{{ route('register', absolute: false) }}" class="mt-2 flex min-h-12 items-center justify-center rounded-lg bg-[#f7941d] px-3 font-semibold text-gray-950" @click="menuOpen = false">অ্যাকাউন্ট খুলুন</a>
            @else
                    <a href="{{ route('dashboard', absolute: false) }}" class="flex min-h-12 items-center rounded-lg px-3 text-gray-800 hover:bg-gray-100" @click="menuOpen = false">আমার অ্যাকাউন্ট</a>
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

    <footer class="mt-16 border-t border-gray-300 bg-white">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-8 text-sm text-gray-600 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <span>© {{ date('Y') }} ঢাকেবির জার্সি হাউজ · Dhakebir Jersey House</span>
            <div class="flex flex-wrap gap-x-4 gap-y-2">
                <a href="{{ route('catalog.index', absolute: false) }}" class="hover:text-[#771115]">সব জার্সি দেখুন</a>
                <a href="{{ route('quotes.create', absolute: false) }}" class="hover:text-[#771115]">৫০+ পিসের কোট</a>
                <a href="{{ route('cms.page', ['slug' => 'return-policy'], absolute: false) }}" class="hover:text-[#771115]">রিটার্ন নীতি</a>
                <a href="{{ route('orders.track', absolute: false) }}" class="hover:text-[#771115]">অর্ডার ট্র্যাক</a>
                <a href="/admin" class="hover:text-[#771115]">অ্যাডমিন</a>
            </div>
        </div>
    </footer>
</body>
</html>
