<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreAttributeValueRequest;
use App\Http\Requests\UpdateAttributeValueRequest;
use App\Models\AttributeValue;

class AttributeValueController extends Controller
{
    public function store(StoreAttributeValueRequest $request)
    {
        $attributeValue = AttributeValue::create($request->validated());

        return response()->json([
            "status" => "success",
            "message" => "Valeur créée avec succès.",
            "data" => $attributeValue,
        ], 201);
    }

    public function update(UpdateAttributeValueRequest $request, AttributeValue $attributeValue)
    {
        $attributeValue->update($request->validated());

        return response()->json([
            "status" => "success",
            "message" => "Valeur mise à jour avec succès.",
            "data" => $attributeValue,
        ], 200);
    }

    public function destroy(AttributeValue $attributeValue)
    {
        if ($attributeValue->productVariants()->exists()) {
            return response()->json([
                "status" => "error",
                "message" => "Impossible de supprimer cette valeur : elle est utilisée par des variantes de produits."
            ], 422);
        }

        $attributeValue->delete();

        return response()->json([
            "status" => "success",
            "message" => "Valeur supprimée avec succès.",
        ], 200);
    }
}
