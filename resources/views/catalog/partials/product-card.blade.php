<article class="jersey-glass-card group min-w-0 overflow-hidden rounded-2xl border border-white/10 bg-slate-900">
    <a href="{{ route('catalog.show', ['slug' => $product->slug], absolute: false) }}" class="relative block aspect-[4/3] overflow-hidden bg-gradient-to-br from-slate-800 via-slate-900 to-amber-950">
        @if ($product->images->isNotEmpty())
            <img src="/storage/{{ ltrim($product->images->first()->image_path, '/') }}" alt="{{ $product->images->first()->alt_text ?: $product->name }}" loading="lazy" class="h-full w-full object-cover transition duration-300 group-hover:scale-105">
        @else
            <div class="flex h-full items-center justify-center">
                <span class="text-6xl text-amber-400/80" aria-hidden="true">✦</span>
            </div>
        @endif
        @if ($product->stock_status === 'preorder')
            <span class="absolute left-3 top-3 rounded-full bg-blue-500/90 px-3 py-1 text-xs font-semibold">প্রি-অর্ডার</span>
        @elseif ($product->stock_status === 'out_of_stock')
            <span class="absolute left-3 top-3 rounded-full bg-slate-700 px-3 py-1 text-xs font-semibold">স্টক শেষ</span>
        @elseif ($product->is_limited_edition)
            <span class="absolute left-3 top-3 rounded-full bg-rose-500/90 px-3 py-1 text-xs font-semibold">লিমিটেড এডিশন</span>
        @endif
    </a>
    <div class="p-3 sm:p-4">
        <p class="text-xs text-amber-300">{{ $product->category->name_bn ?: $product->category->name }}</p>
        <div class="mt-1 flex items-start justify-between gap-2">
            <a href="{{ route('catalog.show', ['slug' => $product->slug], absolute: false) }}" class="min-w-0 break-words text-sm font-semibold leading-6 hover:text-amber-300 sm:text-base">{{ $product->name_bn ?: $product->name }}</a>
            <form action="{{ route('wishlist.toggle', ['product' => $product->id], absolute: false) }}" method="POST">
                @csrf
                <button aria-label="উইশলিস্টে যোগ বা সরান" class="flex min-h-11 min-w-11 shrink-0 items-center justify-center rounded-full text-xl text-rose-300 hover:bg-white/10">♡</button>
            </form>
        </div>
        <div class="mt-2 flex flex-wrap items-center justify-between gap-x-2 gap-y-1 sm:mt-3">
            <p class="font-bold text-amber-300">৳{{ number_format((float) $product->price) }}
                @if ($product->compare_at_price)
                    <span class="ml-1 text-xs font-normal text-slate-500 line-through sm:text-sm">৳{{ number_format((float) $product->compare_at_price) }}</span>
                @endif
            </p>
            <a href="{{ route('catalog.show', ['slug' => $product->slug], absolute: false) }}" class="flex min-h-11 items-center text-sm font-semibold text-slate-700 hover:text-[#771115]">বিস্তারিত →</a>
        </div>
    </div>
</article>
