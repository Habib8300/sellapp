<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="theme-color" content="#771115">
    <meta name="description" content="@yield('description', 'ঢাকেবির জার্সি হাউজ — বিশ্ববিদ্যালয়ের জার্সি, ক্যাম্পাসের গর্ব।')">
    <title>@yield('title', 'ঢাকেবির জার্সি হাউজ') | Dhakebir Jersey House</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="storefront-theme min-h-screen bg-gray-100 text-gray-900 antialiased">
    <header class="sticky top-0 z-30 border-b border-gray-300 bg-white/95 backdrop-blur">
        <div class="mx-auto flex max-w-7xl flex-wrap items-center gap-4 px-4 py-4 sm:px-6">
            <a href="{{ route('home', absolute: false) }}" class="mr-auto text-lg font-bold tracking-tight">
                <span class="text-gray-900">ঢাকেবির</span> জার্সি হাউজ
            </a>
            <form action="{{ route('catalog.index', absolute: false) }}" method="GET" class="order-3 flex w-full sm:order-none sm:w-auto">
                <label class="sr-only" for="store-search">পণ্য খুঁজুন</label>
                <input id="store-search" name="q" value="{{ request('q') }}" placeholder="জার্সি খুঁজুন..." class="w-full rounded-l-lg border-0 bg-white/10 px-3 py-2 text-sm text-white placeholder:text-slate-400 focus:ring-2 focus:ring-amber-400 sm:w-56">
                <button class="rounded-r-lg bg-amber-400 px-4 text-sm font-semibold text-slate-950">খুঁজুন</button>
            </form>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('catalog.index', absolute: false) }}" class="text-slate-200 hover:text-amber-300">শপ</a>
                <a href="{{ route('team-orders.create', absolute: false) }}" class="hidden text-slate-200 hover:text-amber-300 md:inline">টিম অর্ডার</a>
                <a href="{{ route('wishlist.index', absolute: false) }}" class="text-slate-200 hover:text-amber-300">উইশলিস্ট ({{ count(session('wishlist', [])) }})</a>
                <a href="{{ route('cart.index', absolute: false) }}" class="rounded-lg bg-white/10 px-3 py-2 hover:bg-white/15">কার্ট ({{ count(session('cart', [])) }})</a>
                @guest
                    <a href="{{ route('login', absolute: false) }}" class="hidden text-slate-200 hover:text-amber-300 sm:inline">লগ ইন</a>
                    <a href="{{ route('register', absolute: false) }}" class="hidden rounded-lg bg-amber-400 px-3 py-2 font-semibold text-slate-950 hover:bg-amber-300 sm:inline">অ্যাকাউন্ট খুলুন</a>
                @endguest
            </nav>
        </div>
    </header>

    @if (session('status'))
        <div role="status" class="mx-auto mt-4 max-w-7xl px-4 sm:px-6">
            <p class="rounded-lg border border-emerald-400/30 bg-emerald-400/10 px-4 py-3 text-emerald-200">{{ session('status') }}</p>
        </div>
    @endif

    <main>@yield('content')</main>

    <footer class="mt-16 border-t border-white/10">
        <div class="mx-auto flex max-w-7xl flex-col gap-2 px-4 py-8 text-sm text-slate-400 sm:flex-row sm:items-center sm:justify-between sm:px-6">
            <span>© {{ date('Y') }} ঢাকেবির জার্সি হাউজ · Dhakebir Jersey House</span>
            <div class="flex gap-4">
                <a href="{{ route('catalog.index', absolute: false) }}" class="hover:text-amber-300">সব জার্সি দেখুন</a>
                <a href="{{ route('quotes.create', absolute: false) }}" class="hover:text-amber-300">৫০+ পিসের কোট</a>
                <a href="/admin" class="hover:text-amber-300">অ্যাডমিন</a>
            </div>
        </div>
    </footer>
</body>
</html>
