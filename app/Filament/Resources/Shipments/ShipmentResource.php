<?php

namespace App\Filament\Resources\Shipments;

use App\Filament\Resources\Shipments\Pages\ManageShipments;
use App\Models\Shipment;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ShipmentResource extends Resource
{
    protected static ?string $model = Shipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTruck;

    protected static string|\UnitEnum|null $navigationGroup = 'Orders';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Courier tracking')->schema([
                Select::make('order_id')->relationship('order', 'order_number')->searchable()->preload()->required()->unique(ignoreRecord: true),
                Select::make('courier')->options(['Pathao' => 'Pathao', 'Steadfast' => 'Steadfast', 'Other' => 'Other'])->searchable(),
                TextInput::make('tracking_number')->maxLength(120),
                TextInput::make('tracking_url')->url()->maxLength(255),
                Select::make('status')->options([
                    'pending' => 'Pending',
                    'booked' => 'Booked',
                    'picked_up' => 'Picked up',
                    'in_transit' => 'In transit',
                    'delivered' => 'Delivered',
                    'returned' => 'Returned',
                ])->required(),
                TextInput::make('cod_amount')->numeric()->minValue(0)->prefix('৳'),
                DateTimePicker::make('booked_at')->native(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('order.order_number')->label('Order')->searchable(),
            TextColumn::make('order.customer_name')->label('Customer')->searchable(),
            TextColumn::make('courier')->badge(),
            TextColumn::make('tracking_number')->searchable()->copyable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('cod_amount')->money('BDT'),
        ])->recordActions([EditAction::make(), DeleteAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageShipments::route('/')];
    }
}
