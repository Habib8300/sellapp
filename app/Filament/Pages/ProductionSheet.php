<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;

class ProductionSheet extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedDocumentArrowDown;

    protected static string|\UnitEnum|null $navigationGroup = 'Orders';

    protected static ?int $navigationSort = 80;

    protected string $view = 'filament.pages.production-sheet';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }
}
