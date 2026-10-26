<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

class StoreSettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'brand_name' => 'Dhakebir Jersey House',
            'brand_name_bn' => 'ঢাকেবির জার্সি হাউজ',
            'brand_tagline' => 'University jerseys, made for your campus.',
            'brand_primary_color' => '#d97706',
            'brand_logo' => '',
            'default_locale' => 'bn',
            'currency_code' => 'BDT',
            'support_email' => '',
            'support_phone' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
