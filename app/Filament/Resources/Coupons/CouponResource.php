<?php

namespace App\Filament\Resources\Coupons;

use App\Filament\Resources\Coupons\Pages\ManageCoupons;
use App\Models\Coupon;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CouponResource extends Resource
{
    protected static ?string $model = Coupon::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTicket;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Coupon')
                ->schema([
                    TextInput::make('code')->required()->maxLength(40)->unique(ignoreRecord: true)->dehydrateStateUsing(fn (?string $state): string => mb_strtoupper(trim((string) $state))),
                    Select::make('type')->options(['percent' => 'Percentage', 'fixed' => 'Fixed amount'])->required(),
                    TextInput::make('amount')->numeric()->minValue(0.01)->required(),
                    TextInput::make('minimum_order')->numeric()->minValue(0)->default(0)->prefix('৳'),
                    TextInput::make('usage_limit')->numeric()->integer()->minValue(1)->nullable(),
                    DateTimePicker::make('expires_at')->native(false),
                    Toggle::make('student_only'),
                    Toggle::make('is_active')->default(true),
                ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('code')->searchable()->sortable(),
            TextColumn::make('type')->badge(),
            TextColumn::make('amount')->numeric(2),
            TextColumn::make('used_count')->label('Used'),
            TextColumn::make('usage_limit')->label('Limit'),
            IconColumn::make('student_only')->boolean(),
            IconColumn::make('is_active')->boolean(),
            TextColumn::make('expires_at')->dateTime()->placeholder('No expiry'),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageCoupons::route('/')];
    }
}
