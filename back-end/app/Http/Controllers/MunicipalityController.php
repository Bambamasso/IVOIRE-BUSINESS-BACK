<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreMunicipalityRequest;
use App\Http\Requests\UpdateMunicipalityRequest;
use App\Models\Municipality;
use Illuminate\Http\Request;

class MunicipalityController extends Controller
{
    public function index(Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        $query = Municipality::with('city')->orderBy('name', 'asc');

        if ($request->filled('city_id')) {
            $query->where('city_id', $request->query('city_id'));
        }

        return response()->json([
            'status' => 'success',
            'data' => $query->paginate($perPage),
        ], 200);
    }

    public function store(StoreMunicipalityRequest $request)
    {
        $municipality = Municipality::create($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Commune créée avec succès.',
            'data' => $municipality->load('city'),
        ], 201);
    }

    public function show(Municipality $municipality)
    {
        return response()->json([
            'status' => 'success',
            'data' => $municipality->load('city'),
        ], 200);
    }

    public function update(UpdateMunicipalityRequest $request, Municipality $municipality)
    {
        $municipality->update($request->validated());

        return response()->json([
            'status' => 'success',
            'message' => 'Commune mise à jour avec succès.',
            'data' => $municipality->load('city'),
        ], 200);
    }

    public function destroy(Municipality $municipality)
    {
        $municipality->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Commune supprimée avec succès.',
        ], 200);
    }
}
