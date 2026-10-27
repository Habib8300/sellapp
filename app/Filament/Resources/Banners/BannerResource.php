<?php

namespace App\Filament\Resources\Banners;

use App\Filament\Resources\Banners\Pages\ManageBanners;
use App\Models\Banner;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
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

class BannerResource extends Resource
{
    protected static ?string $model = Banner::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedPhoto;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Homepage banner')->schema([
                TextInput::make('title')->required()->maxLength(180),
                TextInput::make('subtitle')->maxLength(255),
                FileUpload::make('image_path')->image()->disk('public')->directory('banners')->visibility('public'),
                TextInput::make('link_url')->url()->maxLength(255),
                TextInput::make('sort_order')->numeric()->integer()->minValue(0)->default(0),
                DateTimePicker::make('starts_at')->native(false),
                DateTimePicker::make('ends_at')->native(false),
                Toggle::make('is_active')->default(true),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->defaultSort('sort_order')
            ->columns([
                TextColumn::make('title')->searchable(),
                TextColumn::make('sort_order')->label('Display order')->sortable(),
                TextColumn::make('starts_at')->dateTime(),
                TextColumn::make('ends_at')->dateTime(),
                IconColumn::make('is_active')->boolean(),
            ])
            ->recordActions([
                Action::make('moveUp')
                    ->label('Move up')
                    ->icon(Heroicon::OutlinedArrowUp)
                    ->color('gray')
                    ->tooltip('Show this banner earlier on the homepage')
                    ->disabled(fn (Banner $record): bool => self::position($record) === 0)
                    ->action(fn (Banner $record) => self::move($record, -1)),
                Action::make('moveDown')
                    ->label('Move down')
                    ->icon(Heroicon::OutlinedArrowDown)
                    ->color('gray')
                    ->tooltip('Show this banner later on the homepage')
                    ->disabled(fn (Banner $record): bool => self::position($record) === self::bannerCount() - 1)
                    ->action(fn (Banner $record) => self::move($record, 1)),
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }

    private static function position(Banner $record): int
    {
        $position = Banner::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->search($record->id);

        return $position === false ? PHP_INT_MAX : $position;
    }

    private static function bannerCount(): int
    {
        return Banner::query()->count();
    }

    private static function move(Banner $record, int $direction): void
    {
        DB::transaction(function () use ($record, $direction): void {
            $banners = Banner::query()
                ->orderBy('sort_order')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();
            $currentPosition = $banners->search(fn (Banner $banner): bool => $banner->is($record));

            if ($currentPosition === false) {
                return;
            }

            $targetPosition = $currentPosition + $direction;

            if (! $banners->has($targetPosition)) {
                return;
            }

            $banners->splice($currentPosition, 1);
            $banners->splice($targetPosition, 0, [$record]);

            foreach ($banners->values() as $position => $banner) {
                $banner->sort_order = $position + 1;
                $banner->save();
            }
        });
    }

    public static function getPages(): array
    {
        return ['index' => ManageBanners::route('/')];
    }
}
