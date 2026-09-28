<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCityRequest;
use App\Http\Requests\UpdateCityRequest;
use App\Models\City;

class CityController extends Controller
{
    public function index()
    {
        $cities = City::withCount('municipalities')->orderBy('name', 'asc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $cities
        ], 200);
    }

    public function store(StoreCityRequest $request)
    {
        $city = City::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Ville créée avec succès.',
            'data' => $city,
        ], 201);
    }

    public function show(City $city)
    {
        return response()->json([
            'status' => 'success',
            'data' => $city->loadCount('municipalities'),
        ], 200);
    }

    public function update(UpdateCityRequest $request, City $city)
    {
        $city->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Ville mise à jour avec succès.',
            'data' => $city,
        ], 200);
    }

    public function destroy(City $city)
    {
        if ($city->municipalities()->exists()) {
            return response()->json([
                'status' => 'error',
                'message' => 'Impossible de supprimer cette ville : elle contient encore des communes.'
            ], 422);
        }

        $city->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Ville supprimée avec succès.',
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
