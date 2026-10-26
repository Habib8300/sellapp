<?php

namespace App\Filament\Resources\Reviews;

use App\Filament\Resources\Reviews\Pages\ManageReviews;
use App\Models\Review;
use BackedEnum;
use Filament\Actions\EditAction;
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

class ReviewResource extends Resource
{
    protected static ?string $model = Review::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedStar;

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin', 'order_manager']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Verified purchase review')->schema([
                TextInput::make('product.name')->label('Product')->disabled(),
                TextInput::make('user.name')->label('Customer')->disabled(),
                TextInput::make('rating')->numeric()->disabled(),
                TextInput::make('title')->disabled(),
                Textarea::make('body')->disabled()->columnSpanFull(),
                TextInput::make('photo_path')->disabled(),
                Toggle::make('is_approved')->label('Publish review'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('product.name')->label('Product')->searchable(),
            TextColumn::make('user.name')->label('Customer')->searchable(),
            TextColumn::make('rating')->sortable(),
            TextColumn::make('title')->limit(40),
            IconColumn::make('is_approved')->boolean(),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageReviews::route('/')];
    }
}
