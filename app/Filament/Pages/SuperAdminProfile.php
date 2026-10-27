<?php

namespace App\Filament\Pages;

use App\Models\User;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class SuperAdminProfile extends Page
{
    protected static ?string $title = 'Super admin profile';

    protected static ?string $navigationLabel = 'Profile';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-user-circle';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 100;

    protected string $view = 'filament.pages.super-admin-profile';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin') ?? false;
    }

    public function mount(): void
    {
        $user = auth()->user();

        $profile = array_filter($user?->only([
            'name',
            'admin_title',
            'phone',
            'admin_bio',
            'timezone',
            'theme',
            'preferred_language',
            'signature',
        ]) ?? [], fn (mixed $value): bool => $value !== null);

        $this->form->fill(array_merge([
            'timezone' => 'Asia/Dhaka',
            'theme' => 'default',
            'preferred_language' => 'bn',
            'new_email' => null,
            'new_password' => null,
            'new_password_confirmation' => null,
            'current_password' => null,
        ], $profile));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Super admin profile')
                    ->schema([
                        TextInput::make('name')->required()->maxLength(255),
                        TextInput::make('admin_title')->label('Professional title')->maxLength(80),
                        TextInput::make('phone')->label('Phone')->maxLength(32),
                        TextInput::make('timezone')->default('Asia/Dhaka')->required()->maxLength(64),
                        Select::make('theme')->options([
                            'default' => 'Default',
                            'dark' => 'Dark',
                            'contrast' => 'Contrast',
                        ])->default('default')->required(),
                        Select::make('preferred_language')->options([
                            'bn' => 'বাংলা',
                            'en' => 'English',
                        ])->default('bn')->required(),
                        Textarea::make('admin_bio')->label('Bio')->rows(4)->maxLength(500),
                        TextInput::make('signature')->label('Signature / note')->maxLength(255),
                    ])
                    ->columns(2),
                Section::make('Email and password')
                    ->description('Your current email is '.auth()->user()?->email.'. Enter your current password before changing account credentials.')
                    ->schema([
                        TextInput::make('new_email')
                            ->label('New email address')
                            ->email()
                            ->nullable()
                            ->maxLength(255)
                            ->rules(fn (): array => [
                                Rule::unique('users', 'email')->ignore(auth()->id()),
                            ]),
                        TextInput::make('new_password')
                            ->label('New password')
                            ->password()
                            ->revealable()
                            ->nullable()
                            ->rules([Password::defaults()])
                            ->same('new_password_confirmation'),
                        TextInput::make('new_password_confirmation')
                            ->label('Confirm new password')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => filled($get('new_password'))),
                        TextInput::make('current_password')
                            ->label('Current password')
                            ->password()
                            ->revealable()
                            ->required(fn (Get $get): bool => filled($get('new_email')) || filled($get('new_password')))
                            ->currentPassword(),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        $user = auth()->user();

        if (! $user instanceof User) {
            return;
        }

        $state = $this->form->getState();
        $newEmail = $state['new_email'] ?? null;
        $newPassword = $state['new_password'] ?? null;
        $credentialsChanged = filled($newEmail) || filled($newPassword);

        if ($credentialsChanged && ! Hash::check((string) ($state['current_password'] ?? ''), $user->password)) {
            Notification::make()
                ->title('Current password is incorrect')
                ->danger()
                ->send();

            return;
        }

        unset(
            $state['new_email'],
            $state['new_password'],
            $state['new_password_confirmation'],
            $state['current_password'],
        );
        $user->fill($state);

        if (filled($newEmail) && $newEmail !== $user->email) {
            $user->email = $newEmail;
            $user->email_verified_at = null;
        }

        if (filled($newPassword)) {
            $user->password = $newPassword;
        }

        $user->save();
        $this->data['new_email'] = null;
        $this->data['new_password'] = null;
        $this->data['new_password_confirmation'] = null;
        $this->data['current_password'] = null;

        if ($credentialsChanged && request()->hasSession()) {
            request()->session()->regenerate();
        }

        Notification::make()
            ->title('Profile updated')
            ->success()
            ->send();
    }
}
