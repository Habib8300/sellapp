@extends('layouts.store')

@section('title', 'শিক্ষার্থী যাচাই')

@section('content')
    <section class="mx-auto max-w-xl px-4 py-10 sm:px-6">
        <h1 class="text-3xl font-bold">শিক্ষার্থী যাচাই</h1>
        @if ($user->student_verified)
            <p class="mt-5 rounded-xl border border-green-300 bg-green-50 p-4 text-green-800">আপনার শিক্ষার্থী পরিচয় যাচাই হয়েছে। checkout-এ শিক্ষার্থী ছাড় স্বয়ংক্রিয়ভাবে প্রয়োগ হবে।</p>
        @else
            @if ($user->student_verification_submitted_at)
                <p class="mt-5 rounded-xl border border-blue-300 bg-blue-50 p-4 text-blue-800">আপনার অনুরোধ {{ $user->student_verification_submitted_at->format('d M Y') }} তারিখে জমা হয়েছে। অ্যাডমিন যাচাই না করা পর্যন্ত ছাড় প্রযোজ্য হবে না।</p>
            @endif
            @if ($errors->any())
                <div class="mt-5 rounded-xl border border-red-300 bg-red-50 p-4 text-red-800">
                    <ul class="list-inside list-disc">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
                </div>
            @endif
            <form method="POST" action="{{ route('student-verification.store', absolute: false) }}" class="mt-6 space-y-4 rounded-2xl border border-gray-200 bg-white p-6 shadow-sm">
                @csrf
                <p class="text-sm text-gray-600">আপনার বিশ্ববিদ্যালয়ের নাম ও শিক্ষার্থী ইমেইল দিন। অ্যাডমিন ম্যানুয়ালি যাচাই করে ছাড় চালু করবেন।</p>
                <div><label for="student-institution">বিশ্ববিদ্যালয় / প্রতিষ্ঠান *</label><input id="student-institution" name="student_institution" value="{{ old('student_institution', $user->student_institution) }}" required maxlength="160" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <div><label for="student-email">শিক্ষার্থী ইমেইল *</label><input id="student-email" type="email" name="student_email" value="{{ old('student_email', $user->student_email) }}" required maxlength="255" class="mt-1 w-full rounded-lg border-gray-300"></div>
                <button class="w-full rounded-lg bg-[#771115] px-5 py-3 font-bold text-white">যাচাইয়ের অনুরোধ জমা দিন</button>
            </form>
        @endif
    </section>
@endsection
