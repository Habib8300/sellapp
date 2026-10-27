<!DOCTYPE html>
<html lang="bn">
<head>
    @php($brandName = \App\Models\Setting::query()->where('key', 'brand_name')->value('value') ?: 'DCU Jersey House')
    <meta charset="utf-8">
    <title>{{ $brandName }} — অর্ডার আপডেট</title>
    <link href="https://fonts.googleapis.com/css2?family=Hind+Siliguri:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body style="margin:0;background:#f3f4f6;color:#1f2937;font-family:'Hind Siliguri',Arial,sans-serif;padding:24px">
    <main style="max-width:600px;margin:auto;background:#fff;border:1px solid #d1d5db;border-radius:12px;padding:24px">
        <p style="color:#771115;font-size:18px;font-weight:bold">ঢাকেবির জার্সি হাউজ · {{ $brandName }}</p>
        <h1 style="font-size:22px">আপনার অর্ডারের আপডেট</h1>
        <p>প্রিয় {{ $order->customer_name }},</p>
        <p>অর্ডার <strong>{{ $order->order_number }}</strong>-এর বর্তমান অবস্থা: <strong>{{ ucfirst(str_replace('_', ' ', $order->status)) }}</strong>।</p>
        <ul>
            @foreach ($order->items as $item)
                <li>{{ $item->product_name }} · {{ $item->size }} / {{ $item->color }} × {{ $item->quantity }}</li>
            @endforeach
        </ul>
        <p>অর্ডারের মোট: ৳{{ number_format((float) $order->total, 2) }}</p>
        <p>ধন্যবাদ,<br>{{ $brandName }}</p>
    </main>
</body>
</html>
