<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ProductVariant;
use Filament\Widgets\Widget;
use Illuminate\Support\Facades\DB;

class DashboardOperations extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = 0;

    protected string $view = 'filament.widgets.dashboard-operations';

    protected int|string|array $columnSpan = 'full';

    public array $dailySales = [];

    public $recentOrders;

    public $lowStockVariants;

    public $topProducts;

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    public function mount(): void
    {
        $days = collect(range(6, 0))->map(fn (int $daysAgo) => now()->startOfDay()->subDays($daysAgo));

        $this->dailySales = $days->map(function ($day): array {
            $orders = Order::query()
                ->whereDate('created_at', $day->toDateString())
                ->whereNotIn('status', ['cancelled', 'returned']);

            return [
                'label' => $day->format('D'),
                'date' => $day->format('M j'),
                'orders' => (clone $orders)->count(),
                'revenue' => (float) (clone $orders)->sum('total'),
            ];
        })->all();

        $this->recentOrders = Order::query()
            ->latest()
            ->limit(8)
            ->get(['id', 'order_number', 'customer_name', 'status', 'payment_status', 'total', 'created_at']);

        $this->lowStockVariants = ProductVariant::query()
            ->with('product:id,name')
            ->where('is_active', true)
            ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
            ->orderBy('stock_quantity')
            ->limit(8)
            ->get(['id', 'product_id', 'sku', 'size', 'color', 'stock_quantity', 'low_stock_threshold']);

        $this->topProducts = OrderItem::query()
            ->select('product_name', DB::raw('SUM(quantity) as units_sold'))
            ->whereHas('order', fn ($query) => $query
                ->where('created_at', '>=', now()->subDays(30)->startOfDay())
                ->whereNotIn('status', ['cancelled', 'returned']))
            ->groupBy('product_name')
            ->orderByDesc('units_sold')
            ->limit(5)
            ->get();
    }
}
