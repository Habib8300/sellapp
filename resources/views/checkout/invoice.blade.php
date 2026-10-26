<!DOCTYPE html>
<html lang="bn">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: HindSiliguri, sans-serif; color: #111827; font-size: 10pt; line-height: 1.35; }
        h1, h2, p { margin: 0; }
        .muted { color: #64748b; }
        .section-title { font-size: 11pt; font-weight: bold; color: #771115; }
        .label { color: #475569; font-weight: bold; }
        .products { border-collapse: collapse; }
        .products th, .products td { border: 1px solid #94a3b8; padding: 6px 5px; vertical-align: top; }
        .products th { background-color: #f1f5f9; font-weight: bold; }
        .products .number { text-align: right; white-space: nowrap; }
        .totals { border-collapse: collapse; }
        .totals td { border: 1px solid #cbd5e1; padding: 5px 7px; }
        .totals .amount { text-align: right; white-space: nowrap; }
        .grand td { background-color: #fff4e8; color: #771115; font-weight: bold; }
    </style>
</head>
<body>
    <table width="100%" cellpadding="0" cellspacing="0" style="border-bottom: 2px solid #771115; margin-bottom: 12px;">
        <tr>
            <td style="padding: 0 0 10px;">
                <h1 style="font-size: 19pt; color: #771115;">{{ $brandName }}</h1>
                <p style="font-size: 13pt;">{{ $brandNameBangla }}</p>
            </td>
            <td width="32%" align="right" style="padding: 0 0 10px;">
                <p style="font-size: 16pt; font-weight: bold;">INVOICE</p>
                <p class="muted">{{ $order->order_number }}</p>
                <p class="muted">{{ $order->created_at->format('d M Y, h:i A') }}</p>
            </td>
        </tr>
    </table>

    <table width="100%" cellpadding="7" cellspacing="0" style="margin: 8px 0 14px;">
        <tr>
            <td width="50%" valign="top" style="border: 1px solid #cbd5e1;">
                <p class="section-title" style="margin-bottom: 5px;">Customer</p>
                <p><span class="label">Name:</span> {{ $order->customer_name }}</p>
                <p><span class="label">Phone:</span> {{ $order->customer_phone }}</p>
                @if ($order->customer_email)<p><span class="label">Email:</span> {{ $order->customer_email }}</p>@endif
            </td>
            <td width="50%" valign="top" style="border: 1px solid #cbd5e1;">
                <p class="section-title" style="margin-bottom: 5px;">Order details</p>
                <p><span class="label">Status:</span> {{ ucfirst($order->status) }}</p>
                <p><span class="label">Payment:</span> {{ strtoupper($order->payment_method) }} ({{ ucfirst($order->payment_status) }})</p>
            @if ($order->fulfillment_method === 'delivery')
                <p><span class="label">Address:</span> {{ $order->delivery_address }}, {{ $order->upazila_name }}, {{ $order->district_name }}, {{ $order->division_name }}</p>
            @else
                <p><span class="label">Pickup:</span> {{ $order->pickup_point }}</p>
            @endif
            </td>
        </tr>
    </table>

    <table class="products" width="100%" cellpadding="0" cellspacing="0">
        <thead>
            <tr>
                <th width="30%" align="left">Product</th>
                <th width="14%" align="left">SKU</th>
                <th width="16%" align="left">Size / Color</th>
                <th width="7%" align="center">Qty</th>
                <th width="16%" align="right">Unit price</th>
                <th width="17%" align="right">Total</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>{{ $item->product_name }}@if ($item->customization)<br><span class="muted">Print: {{ $item->customization['name'] ?? '' }} {{ $item->customization['number'] ?? '' }}</span>@endif</td>
                    <td>{{ $item->product_sku }}</td>
                    <td>{{ $item->size }} / {{ $item->color }}</td>
                    <td align="center">{{ $item->quantity }}</td>
                    <td class="number">BDT {{ number_format((float) $item->unit_price, 2) }}</td>
                    <td class="number">BDT {{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table width="100%" cellpadding="0" cellspacing="0" style="margin-top: 10px;">
        <tr>
            <td width="48%" valign="top" style="padding-top: 10px; color: #64748b;">
                Thank you for shopping with {{ $brandName }}.
            </td>
            <td width="52%" valign="top" align="right">
                <table class="totals" width="100%" cellpadding="0" cellspacing="0">
                    <tr><td>Subtotal</td><td class="amount">BDT {{ number_format((float) $order->subtotal, 2) }}</td></tr>
                    @if ((float) $order->discount_amount > 0)
                        <tr><td>Discount</td><td class="amount">− BDT {{ number_format((float) $order->discount_amount, 2) }}</td></tr>
                    @endif
                    <tr><td>Delivery</td><td class="amount">BDT {{ number_format((float) $order->delivery_fee, 2) }}</td></tr>
                    <tr class="grand"><td>Total</td><td class="amount">BDT {{ number_format((float) $order->total, 2) }}</td></tr>
        @if ($order->advance_payment)
                    <tr><td>Due now</td><td class="amount">BDT {{ number_format((float) $order->amount_due_now, 2) }}</td></tr>
                    <tr><td>Balance due on delivery</td><td class="amount">BDT {{ number_format((float) $order->total - (float) $order->amount_due_now, 2) }}</td></tr>
        @endif
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
