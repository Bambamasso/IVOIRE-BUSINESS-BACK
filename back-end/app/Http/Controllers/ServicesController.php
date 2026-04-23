<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Services;
use App\Models\StatusType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class ServicesController extends Controller
{
    //
    public function index()
    {   $perPage= request()->get('pre_page',4);
        $services = Services::orderBy('created_at', 'desc')->paginate($perPage);
        return response()->json([
            "status" => "success",
            "message" => "Services retrieved successfully",
            "data" => $services
        ], 200);
    }

    public function getServices(){
        $services = Services::orderBy('created_at', 'desc')->get();
        return response()->json([
            "status" => "success",
            "message" => "Services retrieved successfully",
            "data" => $services
        ], 200);
    }
    public function store(Request $request)
    {

        $rules =  [
            "name" => ['required', 'string', 'max:255', Rule::unique('services')->whereNull('deleted_at')],
            "description" => "nullable|string",
            "price" => "required|numeric|min:0",
        ];
        $customMessages=[
            'name.required' => 'Le champ nom est obligatoire.',
            'name.string' => 'Le champ nom doit être une chaîne de caractères.',
            'name.max' => 'Le champ nom ne doit pas dépasser 255 caractères.',
            'name.unique' => 'Le nom du service existe déjà.',
            'description.string' => 'Le champ description doit être une chaîne de caractères.',
            'price.required' => 'Le champ prix est obligatoire.',
            'price.numeric' => 'Le champ prix doit être un nombre.',
            'price.min' => 'Le champ prix doit être supérieur ou égal à 0.',
        ];
    
        $validatedData = Validator::make($request->all(), $rules, $customMessages);
        if ($validatedData->fails()) {
            return response()->json([
                "status" => "error",
                "message" => $validatedData->errors()->first(),
                // "errors" => $validatedData->errors()
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
