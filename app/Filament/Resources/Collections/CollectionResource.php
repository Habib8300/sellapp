<?php

namespace App\Filament\Resources\Collections;

use App\Enums\CollectionType;
use App\Filament\Resources\Collections\Pages\ManageCollections;
use App\Models\Collection;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;

class CollectionResource extends Resource
{
    protected static ?string $model = Collection::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 20;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Collection details')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(120),
                        TextInput::make('name_bn')->label('Name (Bangla)')->maxLength(120),
                        TextInput::make('slug')->required()->maxLength(140)->unique(ignoreRecord: true),
                        Select::make('type')->options(CollectionType::class)->required(),
                        Textarea::make('description')->rows(3)->columnSpanFull(),
                        Toggle::make('is_active')->default(true),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('name_bn')->label('Bangla name')->searchable()->toggleable(),
                TextColumn::make('type')->badge()->sortable(),
                TextColumn::make('slug')->searchable()->toggleable(),
                ToggleColumn::make('is_active'),
                TextColumn::make('products_count')->counts('products')->label('Products')->sortable(),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCollections::route('/'),
        ];
    }
}
