<!DOCTYPE html>
<html lang="bn">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="theme-color" content="#111827">
        <meta name="description" content="ঢাকেবির জার্সি হাউজ — বিশ্ববিদ্যালয়ের জার্সি, ক্যাম্পাসের গর্ব।">
        <title>ঢাকেবির জার্সি হাউজ | Dhakebir Jersey House</title>
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="min-h-screen bg-gray-950 text-white antialiased">
        <header class="mx-auto flex max-w-6xl items-center justify-between px-6 py-6">
            <a href="/" class="text-lg font-bold tracking-tight">
                <span class="text-amber-400">ঢাকেবির</span> জার্সি হাউজ
            </a>
            <nav class="flex items-center gap-3 text-sm">
                <a href="{{ route('login', absolute: false) }}" class="rounded-lg px-4 py-2 text-gray-200 hover:bg-white/10">লগ ইন</a>
                <a href="{{ route('register', absolute: false) }}" class="rounded-lg bg-amber-400 px-4 py-2 font-semibold text-gray-950 hover:bg-amber-300">অ্যাকাউন্ট খুলুন</a>
            </nav>
        </header>

        <main class="mx-auto flex min-h-[75vh] max-w-6xl flex-col justify-center px-6 py-16">
            <p class="mb-5 text-sm font-semibold uppercase tracking-[0.2em] text-amber-400">Dhakebir Jersey House</p>
            <h1 class="max-w-3xl text-4xl font-bold leading-tight sm:text-6xl">
                আপনার ক্যাম্পাস।<br>
                আপনার পরিচয়। <span class="text-amber-400">আপনার জার্সি।</span>
            </h1>
            <p class="mt-6 max-w-xl text-lg leading-8 text-gray-300">
                বিশ্ববিদ্যালয়ের জার্সি হাউজ তৈরি হচ্ছে। অ্যাকাউন্ট খুলুন অথবা স্টোর ব্যবস্থাপনার জন্য অ্যাডমিন লগ ইন করুন।
            </p>
            <div class="mt-9 flex flex-wrap gap-3">
                <a href="{{ route('register', absolute: false) }}" class="rounded-lg bg-amber-400 px-6 py-3 font-semibold text-gray-950 hover:bg-amber-300">শুরু করুন</a>
                <a href="{{ route('login', absolute: false) }}" class="rounded-lg border border-white/20 px-6 py-3 font-semibold hover:bg-white/10">লগ ইন</a>
            </div>
            <p class="mt-16 text-sm text-gray-400">
                দোকান পরিচালনা করেন?
                <a href="/admin" class="text-amber-300 underline underline-offset-4">অ্যাডমিন প্যানেল</a>
            </p>
        </main>

        <footer class="mx-auto max-w-6xl border-t border-white/10 px-6 py-6 text-sm text-gray-400">
            © {{ date('Y') }} ঢাকেবির জার্সি হাউজ · Dhakebir Jersey House
        </footer>
    </body>
</html>
