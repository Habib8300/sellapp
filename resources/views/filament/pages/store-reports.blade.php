<x-filament-panels::page>
    <div class="space-y-8">
        <section class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            @foreach ([
                ['This month orders', $report['orders']],
                ['This month revenue', '৳'.number_format($report['revenue'], 2)],
                ['Discounts given', '৳'.number_format($report['discounts'], 2)],
                ['Customized orders', $report['customizedOrders']],
                ['Products with low-stock variants', $report['lowStock']],
            ] as [$label, $value])
                <article class="rounded-xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-700 dark:bg-gray-900">
                    <p class="text-sm text-gray-500">{{ $label }}</p>
                    <p class="mt-2 text-2xl font-bold">{{ $value }}</p>
                </article>
            @endforeach
        </section>
        <section class="grid gap-6 lg:grid-cols-2">
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <h2 class="bg-gray-50 p-4 font-semibold dark:bg-gray-800">Top products this month</h2>
                <table class="w-full text-sm"><thead><tr class="text-left"><th class="p-3">Product</th><th class="p-3">Units</th><th class="p-3">Sales</th></tr></thead><tbody>
                    @forelse ($report['topProducts'] as $row)<tr class="border-t border-gray-200 dark:border-gray-700"><td class="p-3">{{ $row->product_name }}</td><td class="p-3">{{ $row->units }}</td><td class="p-3">৳{{ number_format((float) $row->revenue, 2) }}</td></tr>@empty<tr><td colspan="3" class="p-3 text-gray-500">No orders this month.</td></tr>@endforelse
                </tbody></table>
            </div>
            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                <h2 class="bg-gray-50 p-4 font-semibold dark:bg-gray-800">Popular sizes this month</h2>
                <table class="w-full text-sm"><thead><tr class="text-left"><th class="p-3">Size</th><th class="p-3">Units</th></tr></thead><tbody>
                    @forelse ($report['topSizes'] as $row)<tr class="border-t border-gray-200 dark:border-gray-700"><td class="p-3">{{ $row->size }}</td><td class="p-3">{{ $row->units }}</td></tr>@empty<tr><td colspan="2" class="p-3 text-gray-500">No orders this month.</td></tr>@endforelse
                </tbody></table>
            </div>
        </section>
    </div>
</x-filament-panels::page>
