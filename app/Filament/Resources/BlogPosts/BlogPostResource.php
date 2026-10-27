<?php

namespace App\Filament\Resources\BlogPosts;

use App\Filament\Resources\BlogPosts\Pages\ManageBlogPosts;
use App\Models\BlogPost;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
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
use Illuminate\Support\Str;

class BlogPostResource extends Resource
{
    protected static ?string $model = BlogPost::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedNewspaper;

    protected static string|\UnitEnum|null $navigationGroup = 'Content';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Blog post')->schema([
                TextInput::make('title')->required()->maxLength(180)->live(onBlur: true)
                    ->afterStateUpdated(fn (?string $state, callable $set): mixed => filled($state) ? $set('slug', Str::slug($state)) : null),
                TextInput::make('slug')->required()->maxLength(200)->unique(ignoreRecord: true),
                Textarea::make('excerpt')->rows(3)->maxLength(1000)->columnSpanFull(),
                Textarea::make('body')->required()->rows(18)->columnSpanFull(),
                FileUpload::make('cover_image')->image()->disk('public')->directory('blog')->visibility('public'),
                TextInput::make('meta_title')->maxLength(255),
                TextInput::make('meta_description')->maxLength(320),
                DateTimePicker::make('published_at')
                    ->native(false)
                    ->helperText('Leave blank to publish immediately when “Published” is enabled. Set a future date to schedule publication.'),
                Toggle::make('is_published')->default(false),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('title')->searchable()->sortable(),
            TextColumn::make('slug')->copyable(),
            TextColumn::make('published_at')->dateTime()->sortable(),
            IconColumn::make('is_published')->boolean(),
        ])->recordActions([EditAction::make(), DeleteAction::make()])
            ->toolbarActions([BulkActionGroup::make([DeleteBulkAction::make()])]);
    }

    public static function getPages(): array
    {
        return ['index' => ManageBlogPosts::route('/')];
    }
}
