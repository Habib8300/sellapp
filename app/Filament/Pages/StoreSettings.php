<?php

namespace App\Filament\Pages;

use App\Models\Setting;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class StoreSettings extends Page
{
    protected static ?string $title = 'Store settings';

    protected static ?string $navigationLabel = 'Store settings';

    protected static string|\BackedEnum|null $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string|\UnitEnum|null $navigationGroup = 'Administration';

    protected static ?int $navigationSort = 90;

    protected string $view = 'filament.pages.store-settings';

    public ?array $data = [];

    public static function canAccess(): bool
    {
        return auth()->user()?->hasAnyRole(['super_admin', 'admin']) ?? false;
    }

    public function mount(): void
    {
        $defaults = [
            'brand_name' => 'Dhakebir Jersey House',
            'brand_name_bn' => 'ঢাকেবির জার্সি হাউজ',
            'brand_tagline' => '',
            'brand_primary_color' => '#d97706',
            'brand_logo' => null,
            'default_locale' => 'bn',
            'currency_code' => 'BDT',
            'support_email' => '',
            'support_phone' => '',
            'delivery_inside_dhaka' => '70',
            'delivery_outside_dhaka' => '130',
            'customization_print_charge' => '150',
            'advance_payment_percent' => '50',
            'student_discount_percent' => '5',
            'payment_bkash_instructions' => '',
            'payment_nagad_instructions' => '',
            'payment_rocket_instructions' => '',
        ];
        $saved = Setting::query()->pluck('value', 'key')->all();

        $this->form->fill(array_replace($defaults, $saved));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Brand')
                    ->schema([
                        TextInput::make('brand_name')->required()->maxLength(120),
                        TextInput::make('brand_name_bn')->label('Brand name (Bangla)')->required()->maxLength(120),
                        TextInput::make('brand_tagline')->maxLength(255),
                        ColorPicker::make('brand_primary_color')->required(),
                        FileUpload::make('brand_logo')
                            ->image()
                            ->disk('public')
                            ->directory('brand')
                            ->visibility('public'),
                    ])
                    ->columns(2),
                Section::make('Store')
                    ->schema([
                        Select::make('default_locale')
                            ->options(['bn' => 'বাংলা', 'en' => 'English'])
                            ->required(),
                        TextInput::make('currency_code')
                            ->label('Currency')
                            ->default('BDT')
                            ->required()
                            ->maxLength(3),
                        TextInput::make('support_email')->email()->maxLength(255),
                        TextInput::make('support_phone')->tel()->maxLength(30),
                    ])
                    ->columns(2),
                Section::make('Checkout and payments')
                    ->schema([
                        TextInput::make('delivery_inside_dhaka')->numeric()->minValue(0)->prefix('৳')->required(),
                        TextInput::make('delivery_outside_dhaka')->numeric()->minValue(0)->prefix('৳')->required(),
                        TextInput::make('customization_print_charge')->numeric()->minValue(0)->prefix('৳')->required(),
                        TextInput::make('advance_payment_percent')
                            ->label('Advance payment (%)')
                            ->numeric()
                            ->minValue(1)
                            ->maxValue(99)
                            ->required(),
                        TextInput::make('student_discount_percent')
                            ->label('Verified student discount (%)')
                            ->numeric()
                            ->minValue(0)
                            ->maxValue(100)
                            ->required(),
                        TextInput::make('payment_bkash_instructions')->label('bKash instructions')->maxLength(500),
                        TextInput::make('payment_nagad_instructions')->label('Nagad instructions')->maxLength(500),
                        TextInput::make('payment_rocket_instructions')->label('Rocket instructions')->maxLength(500),
                    ])
                    ->columns(2),
            ])
            ->statePath('data');
    }

    public function save(): void
    {
        foreach ($this->form->getState() as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_scalar($value) ? (string) $value : null],
            );
        }

        Notification::make()
            ->title('Store settings saved')
            ->success()
            ->send();
    }
}
