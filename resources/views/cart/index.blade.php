@extends('layouts.store')

@section('title', 'আপনার কার্ট')

@section('content')
    <section class="mx-auto max-w-5xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold">আপনার কার্ট</h1>
        @if ($items->isEmpty())
            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900 p-10 text-center">
                <p class="text-xl font-semibold">আপনার কার্ট এখন খালি</p>
                <a href="{{ route('catalog.index', absolute: false) }}" class="mt-5 inline-block rounded-lg bg-amber-400 px-5 py-3 font-bold text-slate-950">জার্সি দেখুন</a>
            </div>
        @else
            <div class="mt-6 space-y-4">
                @foreach ($items as $item)
                    <article class="grid min-w-0 grid-cols-[5rem_minmax(0,1fr)] items-center gap-3 rounded-2xl border border-white/10 bg-slate-900 p-3 sm:grid-cols-[7rem_minmax(0,1fr)_auto] sm:gap-4 sm:p-4">
                        <a href="{{ route('catalog.show', ['slug' => $item['variant']->product->slug], absolute: false) }}" class="aspect-square overflow-hidden rounded-xl bg-slate-800">
                            @if ($item['variant']->product->images->isNotEmpty())
                                <img src="/storage/{{ ltrim($item['variant']->product->images->first()->image_path, '/') }}" alt="{{ $item['variant']->product->name }}" class="h-full w-full object-cover">
                            @else
                                <div class="flex h-full items-center justify-center text-3xl text-amber-300">✦</div>
                            @endif
                        </a>
                        <div>
                            <a href="{{ route('catalog.show', ['slug' => $item['variant']->product->slug], absolute: false) }}" class="font-bold hover:text-amber-300">{{ $item['variant']->product->name_bn ?: $item['variant']->product->name }}</a>
                            <p class="mt-1 text-sm text-slate-400">{{ $item['variant']->size }} · {{ $item['variant']->color }} · ৳{{ number_format($item['unit_price']) }} / পিস</p>
                            @if ($item['customization'])
                                <p class="mt-2 text-sm text-amber-200">{{ $item['customization']['name'] ?? '' }} {{ $item['customization']['number'] ?? '' }} · কাস্টম প্রিন্ট +৳{{ number_format($item['customization_charge']) }}/পিস</p>
                            @endif
                            <form action="{{ route('cart.update', ['line' => $item['key']], absolute: false) }}" method="POST" class="mt-3 flex items-center gap-2">
                                @csrf
                                @method('PATCH')
                                <label for="quantity-{{ $item['variant']->id }}" class="text-sm text-slate-300">পরিমাণ</label>
                                <input id="quantity-{{ $item['variant']->id }}" type="number" name="quantity" min="1" max="20" value="{{ $item['quantity'] }}" class="w-20 rounded-lg border-white/10 bg-slate-950 text-white">
                                <button class="text-sm text-amber-300 hover:text-amber-200">আপডেট</button>
                            </form>
                            @error('quantity')<p class="mt-1 text-sm text-rose-300">{{ $message }}</p>@enderror
                        </div>
                        <div class="col-span-full flex items-center justify-between gap-4 border-t border-white/10 pt-3 sm:col-span-1 sm:flex-col sm:items-end sm:border-0 sm:pt-0">
                            <p class="font-bold text-amber-300">৳{{ number_format($item['line_total']) }}</p>
                            <form action="{{ route('cart.remove', ['line' => $item['key']], absolute: false) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button class="text-sm text-rose-300 hover:text-rose-200">সরান</button>
                            </form>
                        </div>
                    </article>
                @endforeach
            </div>
            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900 p-6">
                <div class="flex justify-between text-lg font-bold"><span>সাবটোটাল</span><span class="text-amber-300">৳{{ number_format($subtotal) }}</span></div>
                <p class="mt-2 text-sm text-slate-400">ডেলিভারি খরচ পরবর্তী ধাপে হিসাব করা হবে।</p>
                <a href="{{ route('checkout.create', absolute: false) }}" class="mt-5 block w-full rounded-lg bg-amber-400 px-5 py-3 text-center font-bold text-slate-950 hover:bg-amber-300">চেকআউটে যান</a>
            </div>
        @endif
    </section>
@endsection
