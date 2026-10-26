@extends('layouts.store')

@section('title', $page->meta_title ?: $page->title)
@section('description', $page->meta_description ?: mb_substr(strip_tags($page->body), 0, 160))

@section('content')
    <article class="mx-auto max-w-4xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold text-[#771115]">{{ $page->title }}</h1>
        <div class="prose mt-6 max-w-none whitespace-pre-line text-gray-800">{{ $page->body }}</div>
    </article>
@endsection
