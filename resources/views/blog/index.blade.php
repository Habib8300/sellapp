@extends('layouts.store')

@section('title', 'জার্সি ব্লগ')
@section('content')
    <section class="mx-auto max-w-7xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold">জার্সি ও ক্যাম্পাস ব্লগ</h1>
        <div class="mt-6 grid gap-5 sm:grid-cols-2 lg:grid-cols-3">
            @forelse ($posts as $post)
                <article class="overflow-hidden rounded-2xl border border-gray-200 bg-white shadow-sm">
                    @if ($post->cover_image)<img src="{{ asset('storage/'.$post->cover_image) }}" alt="{{ $post->title }}" loading="lazy" class="h-48 w-full object-cover">@endif
                    <div class="p-5"><p class="text-xs text-gray-500">{{ $post->published_at->format('d M Y') }}</p><h2 class="mt-2 text-xl font-bold"><a class="hover:text-[#771115]" href="{{ route('blog.show', ['slug' => $post->slug], absolute: false) }}">{{ $post->title }}</a></h2><p class="mt-2 text-sm text-gray-600">{{ $post->excerpt }}</p><a class="mt-4 inline-block font-semibold text-[#771115]" href="{{ route('blog.show', ['slug' => $post->slug], absolute: false) }}">আরও পড়ুন →</a></div>
                </article>
            @empty
                <p class="text-gray-500">ব্লগ পোস্ট শিগগিরই আসছে।</p>
            @endforelse
        </div>
        <div class="mt-6">{{ $posts->links() }}</div>
    </section>
@endsection
