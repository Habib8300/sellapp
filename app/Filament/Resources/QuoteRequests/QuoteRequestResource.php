<?php

namespace App\Filament\Resources\QuoteRequests;

use App\Filament\Resources\QuoteRequests\Pages\ManageQuoteRequests;
use App\Models\QuoteRequest;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class QuoteRequestResource extends Resource
{
    protected static ?string $model = QuoteRequest::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleLeftRight;

    protected static string|\UnitEnum|null $navigationGroup = 'Orders';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Bulk quote request')->schema([
                TextInput::make('name')->disabled(),
                TextInput::make('phone')->tel()->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('department')->disabled(),
                TextInput::make('batch')->disabled(),
                TextInput::make('quantity')->numeric()->disabled(),
                Textarea::make('details')->disabled()->columnSpanFull(),
                Select::make('status')->options([
                    'pending' => 'Pending',
                    'contacted' => 'Contacted',
                    'quoted' => 'Quoted',
                    'closed' => 'Closed',
                ])->required(),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable(),
            TextColumn::make('phone')->searchable(),
            TextColumn::make('department')->placeholder('—'),
            TextColumn::make('quantity')->sortable(),
            TextColumn::make('status')->badge()->sortable(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageQuoteRequests::route('/')];
    }
}
