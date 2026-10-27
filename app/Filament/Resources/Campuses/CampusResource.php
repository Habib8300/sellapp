<?php

namespace App\Filament\Resources\Campuses;

use App\Filament\Resources\Campuses\Pages\ManageCampuses;
use App\Models\Campus;
use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use BackedEnum;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
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
use Illuminate\Support\Str;

class CampusResource extends Resource
{
    protected static ?string $model = Campus::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedMapPin;

    protected static string|\UnitEnum|null $navigationGroup = 'Store';

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Campus details')
                ->schema([
                    TextInput::make('name')
                        ->required()
                        ->maxLength(160)
                        ->live(onBlur: true)
                        ->afterStateUpdated(function (?string $state, callable $set, ?Campus $record): void {
                            if (blank($record?->slug) && filled($state)) {
                                $set('slug', Str::slug($state));
                            }
                        }),
                    TextInput::make('name_bn')->label('Name (Bangla)')->maxLength(160),
                    TextInput::make('slug')->required()->maxLength(180)->unique(ignoreRecord: true),
                    Select::make('division_id')
                        ->options(fn (): array => Division::query()->orderBy('name')->pluck('name', 'id')->all())
                        ->searchable()
                        ->required()
                        ->live()
                        ->afterStateUpdated(function (callable $set): void {
                            $set('district_id', null);
                            $set('upazila_id', null);
                        }),
                    Select::make('district_id')
                        ->options(fn (callable $get): array => District::query()
                            ->where('division_id', $get('division_id'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required()
                        ->live()
                        ->disabled(fn (callable $get): bool => blank($get('division_id')))
                        ->afterStateUpdated(function (callable $set): void {
                            $set('upazila_id', null);
                        }),
                    Select::make('upazila_id')
                        ->options(fn (callable $get): array => Upazila::query()
                            ->where('district_id', $get('district_id'))
                            ->orderBy('name')
                            ->pluck('name', 'id')
                            ->all())
                        ->searchable()
                        ->required()
                        ->disabled(fn (callable $get): bool => blank($get('district_id'))),
                    Textarea::make('address')->rows(3)->columnSpanFull(),
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
                TextColumn::make('district.name')->label('District')->searchable()->sortable(),
                TextColumn::make('upazila.name')->label('Upazila')->searchable()->sortable(),
                TextColumn::make('slug')->searchable()->toggleable(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->defaultSort('name');
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageCampuses::route('/'),
        ];
    }
}
