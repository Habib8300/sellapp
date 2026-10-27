<x-filament-widgets::widget>
    <style>
        .djh-operations {
            display: grid;
            grid-template-columns: repeat(2, minmax(0, 1fr));
            gap: 20px;
            color: #111827;
        }

        .djh-panel {
            min-width: 0;
            overflow: hidden;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            background: #fff;
            box-shadow: 0 2px 8px rgb(15 23 42 / 4%);
        }

        .djh-panel__header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 18px 20px;
            border-bottom: 1px solid #eef0f3;
        }

        .djh-panel__title {
            margin: 0;
            color: #111827;
            font-size: 15px;
            font-weight: 700;
            line-height: 1.4;
        }

        .djh-panel__description {
            margin: 4px 0 0;
            color: #6b7280;
            font-size: 12px;
            line-height: 1.5;
        }

        .djh-panel__link {
            flex: 0 0 auto;
            color: #771115;
            font-size: 13px;
            font-weight: 650;
            text-decoration: none;
        }

        .djh-panel__link:hover {
            color: #a53b1f;
            text-decoration: underline;
        }

        .djh-chart {
            display: grid;
            grid-template-columns: repeat(7, minmax(0, 1fr));
            gap: 8px;
            min-width: 360px;
            padding: 18px 20px 20px;
        }

        .djh-chart__day {
            display: flex;
            min-width: 0;
            flex-direction: column;
            align-items: center;
            gap: 7px;
            text-align: center;
        }

        .djh-chart__orders,
        .djh-chart__revenue {
            color: #6b7280;
            font-size: 11px;
            line-height: 1.3;
        }

        .djh-chart__bar-track {
            display: flex;
            width: 100%;
            height: 118px;
            align-items: flex-end;
            justify-content: center;
            overflow: hidden;
            border-radius: 7px;
            background: #f3f4f6;
        }

        .djh-chart__bar {
            width: 62%;
            min-height: 0;
            border-radius: 6px 6px 0 0;
            background: linear-gradient(180deg, #c45b2a, #771115);
            transition: height .2s ease;
        }

        .djh-chart__label {
            color: #374151;
            font-size: 12px;
            font-weight: 650;
        }

        .djh-table-wrap {
            overflow-x: auto;
        }

        .djh-table {
            width: 100%;
            border-collapse: collapse;
            text-align: left;
            font-size: 13px;
        }

        .djh-table th {
            padding: 11px 16px;
            color: #6b7280;
            background: #fafafa;
            font-size: 10px;
            font-weight: 700;
            letter-spacing: .06em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .djh-table td {
            padding: 12px 16px;
            border-top: 1px solid #f0f1f3;
            color: #374151;
            vertical-align: middle;
        }

        .djh-table tbody tr:hover {
            background: #fffaf5;
        }

        .djh-table__main {
            color: #111827;
            font-weight: 650;
        }

        .djh-table__sub {
            display: block;
            margin-top: 2px;
            color: #6b7280;
            font-size: 11px;
        }

        .djh-table__number {
            text-align: right;
            white-space: nowrap;
        }

        .djh-status {
            display: inline-block;
            padding: 4px 8px;
            border-radius: 999px;
            color: #4b5563;
            background: #f3f4f6;
            font-size: 11px;
            font-weight: 650;
            white-space: nowrap;
        }

        .djh-stock {
            color: #b45309 !important;
            font-weight: 700;
        }

        .djh-empty {
            padding: 28px 16px !important;
            color: #6b7280 !important;
            text-align: center;
        }

        .djh-best-sellers {
            margin: 0;
            padding: 0;
            list-style: none;
        }

        .djh-best-sellers__item {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            padding: 13px 20px;
            border-top: 1px solid #f0f1f3;
            font-size: 13px;
        }

        .djh-best-sellers__item:first-child {
            border-top: 0;
        }

        .djh-best-sellers__name {
            overflow: hidden;
            color: #111827;
            font-weight: 600;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .djh-best-sellers__units {
            flex: 0 0 auto;
            color: #6b7280;
        }

        .djh-best-sellers__empty {
            margin: 0;
            padding: 28px 20px;
            color: #6b7280;
            font-size: 13px;
            text-align: center;
        }

        .dark .djh-operations {
            color: #f9fafb;
        }

        .dark .djh-panel {
            border-color: #374151;
            background: #111827;
            box-shadow: none;
        }

        .dark .djh-panel__header,
        .dark .djh-table td,
        .dark .djh-best-sellers__item {
            border-color: #273244;
        }

        .dark .djh-panel__title,
        .dark .djh-table__main,
        .dark .djh-best-sellers__name {
            color: #f9fafb;
        }

        .dark .djh-table th {
            color: #9ca3af;
            background: #182232;
        }

        .dark .djh-table td {
            color: #d1d5db;
        }

        .dark .djh-table tbody tr:hover {
            background: #1b2533;
        }

        .dark .djh-chart__bar-track,
        .dark .djh-status {
            background: #1f2937;
        }

        .dark .djh-chart__label {
            color: #d1d5db;
        }

        @media (max-width: 900px) {
            .djh-operations {
                grid-template-columns: minmax(0, 1fr);
            }
        }

        @media (max-width: 520px) {
            .djh-operations {
                gap: 14px;
            }

            .djh-panel__header {
                align-items: flex-start;
                padding: 15px;
            }

            .djh-chart {
                padding: 15px;
            }

            .djh-table th,
            .djh-table td {
                padding-right: 11px;
                padding-left: 11px;
            }
        }
    </style>

    <div class="djh-operations">
        <section class="djh-panel">
            <header class="djh-panel__header">
                <div>
                    <h2 class="djh-panel__title">Sales — last 7 days</h2>
                    <p class="djh-panel__description">Revenue excludes cancelled and returned orders.</p>
                </div>
                <a href="{{ url('/admin/store-reports') }}" class="djh-panel__link">Reports</a>
            </header>
            <div class="djh-table-wrap">
                @php($maxRevenue = max(1, max(array_column($dailySales, 'revenue') ?: [0])))
                <div class="djh-chart">
                    @foreach ($dailySales as $day)
                        <div class="djh-chart__day">
                            <span class="djh-chart__orders">{{ $day['orders'] }} orders</span>
                            <div class="djh-chart__bar-track">
                                <span
                                    class="djh-chart__bar"
                                    style="height: {{ $day['revenue'] > 0 ? max(5, (int) (($day['revenue'] / $maxRevenue) * 100)) : 0 }}%"
                                    title="৳{{ number_format($day['revenue'], 2) }}"
                                ></span>
                            </div>
                            <span class="djh-chart__label">{{ $day['label'] }}</span>
                            <span class="djh-chart__revenue">৳{{ number_format($day['revenue'], 0) }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>

        <section class="djh-panel">
            <header class="djh-panel__header">
                <div>
                    <h2 class="djh-panel__title">Recent orders</h2>
                    <p class="djh-panel__description">Latest 8 orders across all statuses.</p>
                </div>
                <a href="{{ url('/admin/orders') }}" class="djh-panel__link">All orders</a>
            </header>
            <div class="djh-table-wrap">
                <table class="djh-table">
                    <thead>
                        <tr><th>Order</th><th>Customer</th><th>Status</th><th class="djh-table__number">Total</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($recentOrders as $order)
                            <tr>
                                <td class="djh-table__main">{{ $order->order_number }}</td>
                                <td>{{ $order->customer_name }}</td>
                                <td><span class="djh-status">{{ str($order->status)->replace('_', ' ')->title() }}</span></td>
                                <td class="djh-table__number">৳{{ number_format((float) $order->total, 2) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="4" class="djh-empty">No orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="djh-panel">
            <header class="djh-panel__header">
                <div>
                    <h2 class="djh-panel__title">Low-stock inventory</h2>
                    <p class="djh-panel__description">Active variants at or below their configured threshold.</p>
                </div>
            </header>
            <div class="djh-table-wrap">
                <table class="djh-table">
                    <thead>
                        <tr><th>Product / SKU</th><th>Variant</th><th class="djh-table__number">Stock / threshold</th></tr>
                    </thead>
                    <tbody>
                        @forelse ($lowStockVariants as $variant)
                            <tr>
                                <td class="djh-table__main">{{ $variant->product?->name }}<span class="djh-table__sub">{{ $variant->sku }}</span></td>
                                <td>{{ $variant->size }} / {{ $variant->color }}</td>
                                <td class="djh-table__number djh-stock">{{ $variant->stock_quantity }} / {{ $variant->low_stock_threshold }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="3" class="djh-empty">No low-stock variants.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>

        <section class="djh-panel">
            <header class="djh-panel__header">
                <div>
                    <h2 class="djh-panel__title">Best sellers — last 30 days</h2>
                    <p class="djh-panel__description">Units from non-cancelled, non-returned orders.</p>
                </div>
                <a href="{{ url('/admin/products') }}" class="djh-panel__link">Catalog</a>
            </header>
            <ol class="djh-best-sellers">
                @forelse ($topProducts as $product)
                    <li class="djh-best-sellers__item">
                        <span class="djh-best-sellers__name">{{ $product->product_name }}</span>
                        <span class="djh-best-sellers__units">{{ $product->units_sold }} sold</span>
                    </li>
                @empty
                    <li class="djh-best-sellers__empty">No sales recorded in the last 30 days.</li>
                @endforelse
            </ol>
        </section>
    </div>
</x-filament-widgets::widget>
