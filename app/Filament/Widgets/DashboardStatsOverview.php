<?php

namespace App\Filament\Widgets;

use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\QuoteRequest;
use App\Models\ReturnRequest;
use App\Models\Review;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class DashboardStatsOverview extends StatsOverviewWidget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -5;

    protected ?string $heading = 'Store overview';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    protected function getStats(): array
    {
        $today = now()->startOfDay();
        $month = now()->startOfMonth();
        $validRevenue = fn ($query) => $query->whereNotIn('status', ['cancelled', 'returned']);

        return [
            Stat::make("Today's orders", Order::query()->where('created_at', '>=', $today)->count())
                ->description('Orders placed today')
                ->color('primary'),
            Stat::make('Pending orders', Order::query()->where('status', 'pending')->count())
                ->description('Need order confirmation')
                ->color('warning'),
            Stat::make('This month revenue', '৳'.number_format((float) $validRevenue(
                Order::query()->where('created_at', '>=', $month)
            )->sum('total'), 2))
                ->description('Excludes cancelled and returned orders')
                ->color('success'),
            Stat::make("Today's revenue", '৳'.number_format((float) $validRevenue(
                Order::query()->where('created_at', '>=', $today)
            )->sum('total'), 2))
                ->description('Excludes cancelled and returned orders')
                ->color('success'),
            Stat::make('Active products', Product::query()->where('is_active', true)->count())
                ->description('Published catalog items')
                ->color('info'),
            Stat::make('Low-stock variants', ProductVariant::query()
                ->where('is_active', true)
                ->whereColumn('stock_quantity', '<=', 'low_stock_threshold')
                ->count())
                ->description('At or below their stock threshold')
                ->color('warning'),
            Stat::make('Open pre-orders', Product::query()
                ->where('is_active', true)
                ->where('is_preorder', true)
                ->where(fn ($query) => $query->whereNull('preorder_close_at')->orWhereDate('preorder_close_at', '>=', today()))
                ->count())
                ->description('Published products accepting pre-orders')
                ->color('info'),
            Stat::make('Customers', User::query()->role('customer')->count())
                ->description('Registered customer accounts')
                ->color('gray'),
            Stat::make('Pending returns', ReturnRequest::query()->where('status', 'pending')->count())
                ->description('Return or exchange requests')
                ->color('warning'),
            Stat::make('Reviews to moderate', Review::query()->where('is_approved', false)->count())
                ->description('Awaiting approval')
                ->color('warning'),
            Stat::make('Open quote requests', QuoteRequest::query()->where('status', 'pending')->count())
                ->description('Bulk order inquiries')
                ->color('info'),
        ];
    }
}
