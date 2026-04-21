<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Http\Request;

class AttributesController extends Controller
{
    //
    public function index()
    {
        $attributes = Attribute::orderBy('created_at', 'desc')->with('values')->get();
        return response()->json([
            "status" => "success",
            "mesage" => "Liste des attributs",
            'data' => $attributes,
        ], 200);
    }

    public function store(Request $request){

    }
    public function show($id){

    }
    public function update(Request $request, $id){

    }
    public function destroy($id){

    }
     public function getAttributeValues($id){
        $attributeValues= AttributeValue::where('attribute_id', $id)->orderBy('created_at', 'desc')->get();
        return response()->json([
            "status" => "success",
            "mesage" => "Liste des valeurs de l'attribut",
            'data' => $attributeValues,
        ], 200);
     }
}
