<?php

namespace App\Filament\Resources\HomeHeroSlides;

use App\Filament\Resources\HomeHeroSlides\Pages\ManageHomeHeroSlides;
use App\Models\HomeHeroSlide;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\FileUpload;
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
use Illuminate\Support\Facades\DB;

class HomeHeroSlideResource extends Resource
{
    protected static ?string $model = HomeHeroSlide::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedRectangleStack;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    protected static ?string $navigationLabel = 'Welcome page slides';

    protected static ?string $modelLabel = 'welcome slide';

    protected static ?string $pluralModelLabel = 'welcome page slides';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Welcome page slide')
                ->description('Add a slide to the first section of the storefront homepage.')
                ->schema([
                    TextInput::make('title')
                        ->label('Headline')
                        ->helperText('Use a new line to split the headline across lines.')
                        ->required()
                        ->maxLength(180)
                        ->columnSpanFull(),
                    Textarea::make('subtitle')
                        ->label('Description')
                        ->rows(4)
                        ->maxLength(2000)
                        ->columnSpanFull(),
                    FileUpload::make('image_path')
                        ->label('Slide image')
                        ->image()
                        ->disk('public')
                        ->directory('home-slides')
                        ->visibility('public')
                        ->imageEditor()
                        ->helperText('Optional. Use a high-quality jersey or campaign image.'),
                    TextInput::make('button_text')
                        ->label('Button text')
                        ->required()
                        ->maxLength(80)
                        ->default('জার্সি দেখুন'),
                    TextInput::make('button_url')
                        ->label('Button link')
                        ->maxLength(255)
                        ->helperText('Use an internal path such as /products or a full HTTPS URL.')
                        ->rule('nullable')
                        ->rules(['regex:/^(?:\/[^\s]*|https:\/\/[^\s]+)$/i']),
                    TextInput::make('sort_order')
                        ->label('Display order')
                        ->numeric()
                        ->integer()
                        ->minValue(0)
                        ->default(0),
                    Toggle::make('is_active')
                        ->label('Show on homepage')
                        ->default(true),
                ])
                ->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')->label('Headline')->searchable()->limit(50),
                TextColumn::make('sort_order')->label('Order')->sortable(),
                IconColumn::make('is_active')->label('Visible')->boolean(),
                TextColumn::make('updated_at')->label('Last updated')->since(),
            ])
            ->recordActions([
                Action::make('moveUp')
                    ->label('Move up')
                    ->icon(Heroicon::OutlinedArrowUp)
                    ->color('gray')
                    ->tooltip('Show this slide earlier on the homepage')
                    ->disabled(fn (HomeHeroSlide $record): bool => self::position($record) === 0)
                    ->action(fn (HomeHeroSlide $record) => self::move($record, -1)),
                Action::make('moveDown')
                    ->label('Move down')
                    ->icon(Heroicon::OutlinedArrowDown)
                    ->color('gray')
                    ->tooltip('Show this slide later on the homepage')
                    ->disabled(fn (HomeHeroSlide $record): bool => self::position($record) === self::slideCount() - 1)
                    ->action(fn (HomeHeroSlide $record) => self::move($record, 1)),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private static function position(HomeHeroSlide $record): int
    {
        $position = HomeHeroSlide::query()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->pluck('id')
            ->search($record->id);

        return $position === false ? PHP_INT_MAX : $position;
    }

    private static function slideCount(): int
    {
        return HomeHeroSlide::query()->count();
    }

    private static function move(HomeHeroSlide $record, int $direction): void
    {
        DB::transaction(function () use ($record, $direction): void {
            $slides = HomeHeroSlide::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $currentPosition = $slides->search(fn (HomeHeroSlide $slide): bool => $slide->is($record));

            if ($currentPosition === false) {
                return;
            }

            $targetPosition = $currentPosition + $direction;

            if (! $slides->has($targetPosition)) {
                return;
            }

            $slides->splice($currentPosition, 1);
            $slides->splice($targetPosition, 0, [$record]);

            foreach ($slides->values() as $position => $slide) {
                $slide->sort_order = $position + 1;
                $slide->save();
            }
        });
    }

    public static function getPages(): array
    {
        return ['index' => ManageHomeHeroSlides::route('/')];
    }
}
