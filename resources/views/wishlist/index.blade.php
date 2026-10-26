@extends('layouts.store')

@section('title', 'উইশলিস্ট')

@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold">আপনার উইশলিস্ট</h1>
        @if ($products->isEmpty())
            <div class="mt-6 rounded-2xl border border-white/10 bg-slate-900 p-10 text-center">
                <p class="text-xl font-semibold">উইশলিস্টে এখনো কোনো পণ্য নেই</p>
                <a href="{{ route('catalog.index', absolute: false) }}" class="mt-5 inline-block rounded-lg bg-amber-400 px-5 py-3 font-bold text-slate-950">জার্সি খুঁজুন</a>
            </div>
        @else
            <div class="mt-6 grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-4">
                @foreach ($products as $product)
                    @include('catalog.partials.product-card', ['product' => $product])
                @endforeach
            </div>
        @endif
    </section>
@endsection
