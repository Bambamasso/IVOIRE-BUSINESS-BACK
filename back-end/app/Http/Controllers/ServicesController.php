<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\StatusType;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ServicesController extends Controller
{
    //
    public function index()
    {
        $services = Services::orderBy('created_at', 'desc')->get();
        return response()->json([
            "status" => "success",
            "message" => "Services retrieved successfully",
            "data" => $services
        ], 200);
    }

    public function store(Request $request)
    {

        $validatedData = validator($request->all(), [
            "name" => ['required', 'string', 'max:255', Rule::unique('services')],
            "description" => "nullable|string",
            "price" => "required|numeric|min:0",
        ]);
    
        if ($validatedData->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "dlfemf",
                "errors" => $validatedData->errors()
            ], 422);
        }
        $service = Services::create($validatedData->validated());
        return response()->json([
            "status" => "success",
            "message" => "Service created successfully",
            "data" => $service
        ], 201);

    }
    public function show(Services $service)
    {

        return response()->json([
            "status" => "success",
            "message" => "Service retrieved successfully",
            "data" => $service
        ], 200);
    }

    public function update(Request $request, Services $service)
    {

        $validatedData = validator($request->all(), [
            "name" => ['nullable', 'string', 'max:255', Rule::unique('services')->ignore($service->id)],
            "description" => "nullable|string",
            "price" => "nullable|numeric|min:0",
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                "status" => "error",
                "message" => $validatedData->errors()->first(),
                "errors" => $validatedData->errors()
            ], 422);
        }

        $service->update($validatedData->validated());

        return response()->json([
            "status" => "success",
            "message" => "Service updated successfully",
            "data" => $service
        ], 200);
    }

    public function destroy(Services $service)
    {

        $service->delete();
        return response()->json([
            "status" => "success",
            "message" => "Service deleted successfully"
        ], 200);
    }

}
