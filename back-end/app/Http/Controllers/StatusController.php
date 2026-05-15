<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Status;
use Illuminate\Http\Request;

class StatusController extends Controller
{
    //
    public function index()
    {

    }
    public function store(Request $request)
    {
        $validatedData = $request->validate([
            "status_type_id" => 'required|exists:status_types,id',
            'name' => 'required|string|max:255',
            'code' => 'required|string|max:255',
        ]);

        $status = Status::create($validatedData);
        return response()->json([
            'status' => 'success',
            'message' => 'Status créé avec succès',
            'data' => $status
        ], 201);
    }

    public function destroy(Status $status)
    {
        $status->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Status supprimé avec succès'
        ], 200);
    }
}
