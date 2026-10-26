@extends('layouts.store')

@section('title', 'অর্ডার '.$order->order_number)

@section('content')
    <section class="mx-auto max-w-3xl px-4 py-12 sm:px-6">
        <div class="rounded-2xl border border-gray-200 bg-white p-6">
            <h1 class="text-2xl font-bold">অর্ডার {{ $order->order_number }}</h1>
            <p class="mt-2 text-gray-600">বর্তমান অবস্থা: <strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong></p>
            @if ($order->shipment)<p class="mt-2 text-sm text-gray-600">Courier: {{ $order->shipment->courier }} · {{ $order->shipment->status }} · Tracking: {{ $order->shipment->tracking_number }} @if ($order->shipment->tracking_url)<a class="text-blue-700 underline" href="{{ $order->shipment->tracking_url }}" rel="noopener" target="_blank">Track</a>@endif</p>@endif
            <ol class="mt-6 space-y-3 border-l-2 border-gray-200 pl-4">
                @foreach ($order->statusHistory as $history)<li><strong>{{ ucfirst(str_replace('_', ' ', $history->status)) }}</strong><span class="ml-2 text-sm text-gray-500">{{ $history->created_at->format('d M Y, h:i A') }}</span>@if ($history->note)<p class="text-sm text-gray-600">{{ $history->note }}</p>@endif</li>@endforeach
            </ol>
            <h2 class="mt-6 font-semibold">পণ্য</h2>
            @foreach ($order->items as $item)<p class="mt-2 text-sm">{{ $item->product_name }} · {{ $item->size }} / {{ $item->color }} × {{ $item->quantity }}</p>@endforeach
        </div>
    </section>
@endsection
