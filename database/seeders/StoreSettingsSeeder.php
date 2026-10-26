<?php

namespace Database\Seeders;

use App\Models\BulkDiscountSlab;
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
            'delivery_inside_dhaka' => '70',
            'delivery_outside_dhaka' => '130',
            'customization_print_charge' => '150',
            'advance_payment_percent' => '50',
            'student_discount_percent' => '5',
            'payment_bkash_instructions' => '',
            'payment_nagad_instructions' => '',
            'payment_rocket_instructions' => '',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }

        BulkDiscountSlab::firstOrCreate(
            ['minimum_quantity' => 10],
            ['discount_percent' => 5, 'is_active' => true],
        );
        BulkDiscountSlab::firstOrCreate(
            ['minimum_quantity' => 25],
            ['discount_percent' => 10, 'is_active' => true],
        );
    }
}
