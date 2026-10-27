<?php

namespace Database\Seeders;

use App\Models\District;
use App\Models\Division;
use App\Models\Upazila;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use RuntimeException;

class BangladeshLocationSeeder extends Seeder
{
    public function run(): void
    {
        // Snapshot sourced from the public BDAPIS dataset: https://bdapis.com/api/v1.2
        $path = database_path('seeders/data/bangladesh_locations.json');
        $divisions = json_decode(file_get_contents($path), true, flags: JSON_THROW_ON_ERROR);

        if (count($divisions) !== 8) {
            throw new RuntimeException('The Bangladesh location dataset must contain all 8 divisions.');
        }

        foreach ($divisions as $divisionData) {
            $division = Division::updateOrCreate(
                ['slug' => Str::slug($divisionData['name'])],
                ['name' => $divisionData['name'], 'name_bn' => $divisionData['name_bn']],
            );

            foreach ($divisionData['districts'] as $districtData) {
                $district = District::updateOrCreate(
                    [
                        'division_id' => $division->id,
                        'slug' => Str::slug($districtData['name']),
                    ],
                    [
                        'name' => $districtData['name'],
                        'name_bn' => $districtData['name_bn'],
                    ],
                );

                foreach ($districtData['upazilas'] as $upazilaName) {
                    Upazila::updateOrCreate(
                        [
                            'district_id' => $district->id,
                            'slug' => Str::slug($upazilaName),
                        ],
                        ['name' => $upazilaName, 'location_type' => 'upazila'],
                    );
                }
            }
        }

        $cities = [
            ['district' => 'Dhaka', 'name' => 'Dhaka North City Corporation', 'name_bn' => 'ঢাকা উত্তর সিটি কর্পোরেশন', 'slug' => 'dhaka-north-city'],
            ['district' => 'Dhaka', 'name' => 'Dhaka South City Corporation', 'name_bn' => 'ঢাকা দক্ষিণ সিটি কর্পোরেশন', 'slug' => 'dhaka-south-city'],
            ['district' => 'Chattogram', 'name' => 'Chattogram City Corporation', 'name_bn' => 'চট্টগ্রাম সিটি কর্পোরেশন', 'slug' => 'chattogram-city'],
            ['district' => 'Khulna', 'name' => 'Khulna City Corporation', 'name_bn' => 'খুলনা সিটি কর্পোরেশন', 'slug' => 'khulna-city'],
            ['district' => 'Rajshahi', 'name' => 'Rajshahi City Corporation', 'name_bn' => 'রাজশাহী সিটি কর্পোরেশন', 'slug' => 'rajshahi-city'],
            ['district' => 'Barishal', 'name' => 'Barishal City Corporation', 'name_bn' => 'বরিশাল সিটি কর্পোরেশন', 'slug' => 'barishal-city'],
            ['district' => 'Sylhet', 'name' => 'Sylhet City Corporation', 'name_bn' => 'সিলেট সিটি কর্পোরেশন', 'slug' => 'sylhet-city'],
            ['district' => 'Rangpur', 'name' => 'Rangpur City Corporation', 'name_bn' => 'রংপুর সিটি কর্পোরেশন', 'slug' => 'rangpur-city'],
            ['district' => 'Gazipur', 'name' => 'Gazipur City Corporation', 'name_bn' => 'গাজীপুর সিটি কর্পোরেশন', 'slug' => 'gazipur-city'],
            ['district' => 'Narayanganj', 'name' => 'Narayanganj City Corporation', 'name_bn' => 'নারায়ণগঞ্জ সিটি কর্পোরেশন', 'slug' => 'narayanganj-city'],
            ['district' => 'Cumilla', 'name' => 'Cumilla City Corporation', 'name_bn' => 'কুমিল্লা সিটি কর্পোরেশন', 'slug' => 'cumilla-city'],
            ['district' => 'Mymensingh', 'name' => 'Mymensingh City Corporation', 'name_bn' => 'ময়মনসিংহ সিটি কর্পোরেশন', 'slug' => 'mymensingh-city'],
        ];

        foreach ($cities as $city) {
            $district = District::query()->where('name', $city['district'])->firstOrFail();
            Upazila::updateOrCreate(
                ['district_id' => $district->id, 'slug' => $city['slug']],
                [
                    'name' => $city['name'],
                    'name_bn' => $city['name_bn'],
                    'location_type' => 'city',
                ],
            );
        }
    }
}
