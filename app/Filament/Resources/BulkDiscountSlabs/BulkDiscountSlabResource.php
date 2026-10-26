<?php

namespace App\Filament\Resources\BulkDiscountSlabs;

use App\Filament\Resources\BulkDiscountSlabs\Pages\ManageBulkDiscountSlabs;
use App\Models\BulkDiscountSlab;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class BulkDiscountSlabResource extends Resource
{
    protected static ?string $model = BulkDiscountSlab::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedReceiptPercent;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Quantity discount')->schema([
                TextInput::make('minimum_quantity')->label('Minimum pieces')->numeric()->integer()->minValue(2)->required()->unique(ignoreRecord: true),
                TextInput::make('discount_percent')->numeric()->minValue(0.01)->maxValue(100)->suffix('%')->required(),
                Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('minimum_quantity')->label('Minimum pieces')->sortable(),
            TextColumn::make('discount_percent')->suffix('%'),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBulkDiscountSlabs::route('/')];
    }
}
