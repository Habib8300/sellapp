<?php

namespace App\Filament\Resources\Products;

use App\Enums\ProductFit;
use App\Filament\Resources\Products\Pages\ManageProducts;
use App\Models\Product;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\RestoreAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
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
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Str;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Catalog';

    protected static ?int $navigationSort = 30;

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Product details')
                    ->schema([
                        Select::make('category_id')
                            ->relationship('category', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        Select::make('collections')
                            ->relationship('collections', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload(),
                        TextInput::make('name')
                            ->required()
                            ->maxLength(180)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (?string $state, callable $set, ?Product $record): void {
                                if (blank($record?->slug) && filled($state)) {
                                    $set('slug', Str::slug($state));
                                }
                            }),
                        TextInput::make('name_bn')->label('Name (Bangla)')->maxLength(180),
                        TextInput::make('slug')->required()->maxLength(200)->unique(ignoreRecord: true),
                        TextInput::make('sku')->required()->maxLength(80)->unique(ignoreRecord: true),
                        Textarea::make('description')->rows(5)->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Jersey specifications')
                    ->schema([
                        TextInput::make('fabric_type')->maxLength(100),
                        TextInput::make('gsm')->numeric()->integer()->minValue(1)->maxValue(1000),
                        Select::make('fit')->options(ProductFit::class),
                        Select::make('collar_type')
                            ->options(['round' => 'Round neck', 'v_neck' => 'V-neck', 'polo' => 'Polo', 'collar' => 'Collar', 'other' => 'Other'])
                            ->searchable(),
                        Select::make('sleeve_type')
                            ->options(['short' => 'Short sleeve', 'long' => 'Long sleeve', 'sleeveless' => 'Sleeveless'])
                            ->searchable(),
                        TextInput::make('price')->numeric()->minValue(0)->required()->prefix('৳'),
                        TextInput::make('compare_at_price')->numeric()->minValue(0)->prefix('৳'),
                        Toggle::make('is_active')->label('Published')->default(false),
                        Toggle::make('is_limited_edition'),
                        Toggle::make('is_preorder')->live(),
                        DatePicker::make('preorder_estimated_delivery_at')
                            ->visible(fn (callable $get): bool => (bool) $get('is_preorder'))
                            ->required(fn (callable $get): bool => (bool) $get('is_preorder'))
                            ->native(false),
                        DatePicker::make('preorder_close_at')
                            ->visible(fn (callable $get): bool => (bool) $get('is_preorder'))
                            ->required(fn (callable $get): bool => (bool) $get('is_preorder'))
                            ->native(false)
                            ->afterOrEqual('today'),
                        Textarea::make('size_guide')->label('How to measure')->rows(3)->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make('Product images')
                    ->description('Upload front, back, and detail images. Files are stored on the public disk.')
                    ->schema([
                        Repeater::make('images')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->schema([
                                FileUpload::make('image_path')
                                    ->image()
                                    ->disk('public')
                                    ->directory('catalog/products')
                                    ->visibility('public')
                                    ->required(),
                                Select::make('view')
                                    ->options(['front' => 'Front', 'back' => 'Back', 'detail' => 'Detail', 'other' => 'Other'])
                                    ->required(),
                                TextInput::make('alt_text')->maxLength(255),
                            ])
                            ->columns(3)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
                Section::make('Variants and inventory')
                    ->description('Set stock independently for each size and color. Stock changes are recorded in the inventory history.')
                    ->schema([
                        Repeater::make('variants')
                            ->relationship()
                            ->schema([
                                TextInput::make('sku')->required()->maxLength(80),
                                Select::make('size')
                                    ->options(['XS' => 'XS', 'S' => 'S', 'M' => 'M', 'L' => 'L', 'XL' => 'XL', 'XXL' => 'XXL', '3XL' => '3XL', 'Kids' => 'Kids'])
                                    ->required()
                                    ->searchable(),
                                TextInput::make('color')->required()->maxLength(60),
                                ColorPicker::make('color_code'),
                                TextInput::make('price_adjustment')->numeric()->default(0)->prefix('৳'),
                                TextInput::make('stock_quantity')->numeric()->integer()->minValue(0)->default(0)->required(),
                                TextInput::make('low_stock_threshold')->numeric()->integer()->minValue(0)->default(5)->required(),
                                Toggle::make('is_active')->default(true),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
                Section::make('Size chart')
                    ->schema([
                        Repeater::make('sizeCharts')
                            ->relationship()
                            ->orderColumn('sort_order')
                            ->schema([
                                Select::make('size')
                                    ->options(['XS' => 'XS', 'S' => 'S', 'M' => 'M', 'L' => 'L', 'XL' => 'XL', 'XXL' => 'XXL', '3XL' => '3XL', 'Kids' => 'Kids'])
                                    ->required()
                                    ->searchable(),
                                TextInput::make('chest_inches')->numeric()->minValue(0)->label('Chest (in)'),
                                TextInput::make('length_inches')->numeric()->minValue(0)->label('Length (in)'),
                                TextInput::make('shoulder_inches')->numeric()->minValue(0)->label('Shoulder (in)'),
                                TextInput::make('chest_cm')->numeric()->minValue(0)->label('Chest (cm)'),
                                TextInput::make('length_cm')->numeric()->minValue(0)->label('Length (cm)'),
                                TextInput::make('shoulder_cm')->numeric()->minValue(0)->label('Shoulder (cm)'),
                            ])
                            ->columns(4)
                            ->defaultItems(0)
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with('variants'))
            ->columns([
                TextColumn::make('name')->searchable()->sortable(),
                TextColumn::make('sku')->searchable()->copyable(),
                TextColumn::make('category.name')->sortable(),
                TextColumn::make('price')->money('BDT')->sortable(),
                TextColumn::make('stock_status')
                    ->label('Stock')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'in_stock' => 'success',
                        'low_stock' => 'warning',
                        'preorder' => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('variants_count')->counts('variants')->label('Variants'),
                ToggleColumn::make('is_active')->label('Published'),
                TextColumn::make('created_at')->dateTime()->sortable()->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('category')->relationship('category', 'name')->searchable()->preload(),
                SelectFilter::make('is_active')->options([1 => 'Published', 0 => 'Draft']),
                TrashedFilter::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
                RestoreAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => ManageProducts::route('/'),
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
