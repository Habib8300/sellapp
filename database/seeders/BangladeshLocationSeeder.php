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
                        ['name' => $upazilaName],
                    );
                }
            }
        }
    }
}
