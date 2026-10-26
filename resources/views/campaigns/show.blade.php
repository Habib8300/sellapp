@extends('layouts.store')

@section('title', $campaign->name)

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <div class="rounded-3xl border border-gray-200 bg-white p-7 shadow-sm sm:p-10">
            <p class="text-sm font-semibold uppercase tracking-wide text-[#f7941d]">Batch / Department Campaign</p>
            <h1 class="mt-3 text-3xl font-bold text-[#771115]">{{ $campaign->name }}</h1>
            @if ($campaign->department || $campaign->batch)
                <p class="mt-2 text-gray-600">{{ $campaign->department }} @if ($campaign->department && $campaign->batch) · @endif {{ $campaign->batch }}</p>
            @endif
            @if ($campaign->description)<p class="mt-5 whitespace-pre-line text-gray-700">{{ $campaign->description }}</p>@endif
            <p class="mt-5 rounded-xl bg-gray-100 p-4">অর্ডারের শেষ সময়: <strong>{{ $campaign->deadline->format('d M Y, h:i A') }}</strong></p>
            @if ($campaign->product)
                <a href="{{ route('catalog.show', ['slug' => $campaign->product->slug], absolute: false) }}" class="mt-6 inline-block rounded-lg bg-[#771115] px-5 py-3 font-semibold text-white">এই ক্যাম্পেইনের ডিজাইন দেখুন</a>
            @else
                <a href="{{ route('catalog.index', absolute: false) }}" class="mt-6 inline-block rounded-lg bg-[#771115] px-5 py-3 font-semibold text-white">জার্সি বেছে নিন</a>
            @endif
            <p class="mt-4 text-sm text-gray-500">এই লিংক থেকে অর্ডার করলে সেটি ক্যাম্পেইনের মোট অর্ডারে যুক্ত হবে।</p>
        </div>
    </section>
@endsection
