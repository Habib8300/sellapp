@extends('layouts.store')

@section('title', 'বাল্ক অর্ডার কোট')

@section('content')
    <section class="mx-auto max-w-2xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold">৫০+ পিসের কোট নিন</h1>
        <p class="mt-2 text-gray-600">আপনার টিম বা বিভাগের তথ্য দিন; আমাদের টিম মূল্য ও ডেলিভারি নিয়ে যোগাযোগ করবে।</p>
        @if ($errors->any())
            <div class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
            </div>
        @endif
        <form method="POST" action="{{ route('quotes.store', absolute: false) }}" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
            @csrf
            <div><label for="quote-name">নাম *</label><input id="quote-name" name="name" value="{{ old('name') }}" required maxlength="150" class="mt-1 w-full rounded-lg border-gray-300"></div>
            <div><label for="quote-phone">ফোন *</label><input id="quote-phone" name="phone" value="{{ old('phone') }}" required placeholder="01XXXXXXXXX" class="mt-1 w-full rounded-lg border-gray-300"></div>
            <div><label for="quote-email">ইমেইল</label><input id="quote-email" name="email" type="email" value="{{ old('email') }}" class="mt-1 w-full rounded-lg border-gray-300"></div>
            <div class="grid gap-4 sm:grid-cols-2">
                <div><label for="quote-department">বিভাগ / ক্লাব</label><input id="quote-department" name="department" value="{{ old('department') }}" maxlength="160" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div><label for="quote-batch">ব্যাচ</label><input id="quote-batch" name="batch" value="{{ old('batch') }}" maxlength="80" class="mt-1 w-full rounded-lg border-gray-300"></div>
            </div>
            <div><label for="quote-quantity">পরিমাণ (কমপক্ষে ৫০) *</label><input id="quote-quantity" name="quantity" type="number" min="50" max="10000" value="{{ old('quantity', 50) }}" required class="mt-1 w-full rounded-lg border-gray-300"></div>
            <div><label for="quote-details">ডিজাইন / সাইজ সংক্রান্ত তথ্য</label><textarea id="quote-details" name="details" rows="4" maxlength="3000" class="mt-1 w-full rounded-lg border-gray-300">{{ old('details') }}</textarea></div>
            <button class="w-full rounded-lg bg-[#771115] px-5 py-3 font-bold text-white">কোট অনুরোধ জমা দিন</button>
        </form>
    </section>
@endsection
