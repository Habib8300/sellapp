<?php

namespace App\Filament\Pages;

use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Facades\DB;

class StoreReports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|\UnitEnum|null $navigationGroup = 'Reports';

    protected static ?int $navigationSort = 10;

    protected string $view = 'filament.pages.store-reports';

    public array $report = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    public function mount(): void
    {
        $from = now()->startOfMonth();
        $this->report = [
            'orders' => Order::query()->where('created_at', '>=', $from)->count(),
            'revenue' => (float) Order::query()->where('created_at', '>=', $from)->whereNotIn('status', ['cancelled', 'returned'])->sum('total'),
            'discounts' => (float) Order::query()->where('created_at', '>=', $from)->sum('discount_amount'),
            'topProducts' => OrderItem::query()->select('product_name', DB::raw('SUM(quantity) as units'), DB::raw('SUM(line_total) as revenue'))
                ->whereHas('order', fn ($query) => $query->where('created_at', '>=', $from)->whereNotIn('status', ['cancelled', 'returned']))
                ->groupBy('product_name')->orderByDesc('units')->limit(10)->get(),
            'topSizes' => OrderItem::query()->select('size', DB::raw('SUM(quantity) as units'))
                ->whereHas('order', fn ($query) => $query->where('created_at', '>=', $from)->whereNotIn('status', ['cancelled', 'returned']))
                ->groupBy('size')->orderByDesc('units')->limit(10)->get(),
            'customizedOrders' => Order::query()->where('created_at', '>=', $from)
                ->whereHas('items', fn ($query) => $query->whereNotNull('customization'))->count(),
            'lowStock' => Product::query()->whereHas('variants', fn ($query) => $query->where('is_active', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold'))->count(),
        ];
    }
}
