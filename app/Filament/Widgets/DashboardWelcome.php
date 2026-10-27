<?php

namespace App\Filament\Widgets;

use Filament\Widgets\Widget;

class DashboardWelcome extends Widget
{
    protected static bool $isLazy = false;

    protected static ?int $sort = -10;

    protected string $view = 'filament.widgets.dashboard-welcome';

    protected int|string|array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    protected function getViewData(): array
    {
        return [
            'user' => auth()->user(),
            'brandName' => config('app.name', 'DCU Jersey House'),
        ];
    }
}
