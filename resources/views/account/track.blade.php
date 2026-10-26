@extends('layouts.store')

@section('title', 'অর্ডার ট্র্যাক করুন')

@section('content')
    <section class="mx-auto max-w-xl px-4 py-12 sm:px-6">
        <h1 class="text-3xl font-bold">অর্ডার ট্র্যাক করুন</h1>
        <p class="mt-2 text-gray-600">আপনার অর্ডার নম্বর ও অর্ডারের সময় ব্যবহৃত ফোন নম্বর লিখুন।</p>
        @if ($errors->any())<div class="mt-4 rounded-lg bg-red-50 p-3 text-red-800">@foreach ($errors->all() as $error)<p>{{ $error }}</p>@endforeach</div>@endif
        <form method="POST" action="{{ route('orders.track.submit', absolute: false) }}" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6">
            @csrf
            <label class="block text-sm">অর্ডার নম্বর<input name="order_number" value="{{ old('order_number') }}" required maxlength="32" class="mt-1 w-full rounded-lg border-gray-300"></label>
            <label class="block text-sm">ফোন নম্বর<input name="phone" value="{{ old('phone') }}" required maxlength="30" class="mt-1 w-full rounded-lg border-gray-300"></label>
            <button class="w-full rounded-lg bg-[#771115] px-4 py-3 font-bold text-white">অর্ডার খুঁজুন</button>
        </form>
    </section>
@endsection
