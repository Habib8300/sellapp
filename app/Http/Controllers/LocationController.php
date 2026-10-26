<?php

namespace App\Http\Controllers;

use App\Models\District;
use App\Models\Upazila;
use Illuminate\Http\JsonResponse;

class LocationController extends Controller
{
    public function districts(int $divisionId): JsonResponse
    {
        return response()->json(
            District::query()
                ->where('division_id', $divisionId)
                ->orderBy('name')
                ->get(['id', 'name', 'name_bn']),
        );
    }

    public function upazilas(int $districtId): JsonResponse
    {
        return response()->json(
            Upazila::query()
                ->where('district_id', $districtId)
                ->orderBy('name')
                ->get(['id', 'name', 'name_bn']),
        );
    }
}
