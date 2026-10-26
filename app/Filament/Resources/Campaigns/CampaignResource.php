<?php

namespace App\Filament\Resources\Campaigns;

use App\Filament\Resources\Campaigns\Pages\ManageCampaigns;
use App\Models\Campaign;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;

class CampaignResource extends Resource
{
    protected static ?string $model = Campaign::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMegaphone;

    protected static string|\UnitEnum|null $navigationGroup = 'Marketing';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Batch campaign')->schema([
                TextInput::make('name')->required()->maxLength(160)->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, callable $set): mixed => filled($state) ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')->required()->maxLength(180)->unique(ignoreRecord: true),
                TextInput::make('department')->maxLength(160),
                TextInput::make('batch')->maxLength(80),
                Select::make('product_id')->relationship('product', 'name')->searchable()->preload(),
                DateTimePicker::make('deadline')->required()->native(false),
                Textarea::make('description')->rows(4)->columnSpanFull(),
                Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('department')->placeholder('—'),
            TextColumn::make('batch')->placeholder('—'),
            TextColumn::make('deadline')->dateTime()->sortable(),
            TextColumn::make('campaign_orders_sum_quantity')->label('Pieces ordered')->sortable(),
            TextColumn::make('slug')->label('Public link')->copyable(),
            IconColumn::make('is_active')->boolean(),
        ])->recordActions([
            EditAction::make(),
            DeleteAction::make(),
        ])->toolbarActions([
            BulkActionGroup::make([DeleteBulkAction::make()]),
        ]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->withSum('campaignOrders', 'quantity');
    }

    public static function getPages(): array
    {
        return ['index' => ManageCampaigns::route('/')];
    }
}
