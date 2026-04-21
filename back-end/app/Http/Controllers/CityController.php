<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\City;
use Illuminate\Http\Request;

class CityController extends Controller
{
    //
    public function index()
    {
        $cities = City::orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $cities
        ], 200);
    }

    public function getMunicipalityByCity($cityId)
    {
        $city = City::with('municipalities')->find($cityId);

        if (!$city) {
            return response()->json([
                'status' => 'error',
                'message' => 'City not found'
            ], 404);
        }

        return response()->json([
            'status' => 'success',
            'data' => $city->municipalities
        ], 200);
    }
}
