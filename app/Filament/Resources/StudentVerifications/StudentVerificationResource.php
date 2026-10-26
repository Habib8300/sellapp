<?php

namespace App\Filament\Resources\StudentVerifications;

use App\Filament\Resources\StudentVerifications\Pages\ManageStudentVerifications;
use App\Models\User;
use BackedEnum;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\ToggleColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class StudentVerificationResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedAcademicCap;

    protected static string|\UnitEnum|null $navigationGroup = 'Customers';

    public static function canViewAny(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Student verification')->schema([
                TextInput::make('name')->disabled(),
                TextInput::make('email')->disabled(),
                TextInput::make('student_institution')->label('Institution')->disabled(),
                TextInput::make('student_email')->label('Student email')->disabled(),
                TextInput::make('student_verification_submitted_at')->label('Submitted at')->disabled(),
                Toggle::make('student_verified')->label('Verified student')->helperText('Verified students receive the configured student discount at checkout.'),
            ])->columns(2),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table->columns([
            TextColumn::make('name')->searchable()->sortable(),
            TextColumn::make('email')->searchable(),
            TextColumn::make('student_institution')->label('Institution')->searchable(),
            TextColumn::make('student_email')->label('Student email')->searchable(),
            TextColumn::make('student_verification_submitted_at')->dateTime()->sortable()->placeholder('Not submitted'),
            ToggleColumn::make('student_verified')->label('Verified'),
            TextColumn::make('created_at')->dateTime()->sortable(),
        ])->recordActions([EditAction::make()]);
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()->whereHas('roles', fn (Builder $query) => $query->where('name', 'customer'));
    }

    public static function getPages(): array
    {
        return ['index' => ManageStudentVerifications::route('/')];
    }
}
