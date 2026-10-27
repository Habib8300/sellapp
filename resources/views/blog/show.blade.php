@extends('layouts.store')

@section('title', $post->meta_title ?: $post->title)
@section('description', $post->meta_description ?: ($post->excerpt ?: ''))
@section('content')
    <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        @if ($post->cover_image)<img src="{{ asset('storage/'.$post->cover_image) }}" alt="{{ $post->title }}" class="mb-6 max-h-[32rem] w-full rounded-2xl object-cover">@endif
        <p class="text-sm text-gray-500">{{ ($post->published_at ?? $post->created_at)->format('d M Y') }}</p>
        <h1 class="mt-2 text-3xl font-bold text-[#771115]">{{ $post->title }}</h1>
        @if ($post->excerpt)<p class="mt-3 text-lg text-gray-600">{{ $post->excerpt }}</p>@endif
        <div class="prose mt-8 max-w-none whitespace-pre-line text-gray-800">{{ $post->body }}</div>
    </article>
@endsection
