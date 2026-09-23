<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreAttributeRequest;
use App\Http\Requests\UpdateAttributeRequest;
use App\Models\Attribute;
use App\Models\AttributeValue;
use Illuminate\Support\Str;

class AttributesController extends Controller
{
    public function index()
    {
        $attributes = Attribute::withCount('values')->orderBy('created_at', 'desc')->with('values')->get();
        return response()->json([
            "status" => "success",
            "mesage" => "Liste des caractéristiques",
            'data' => $attributes,
        ], 200);
    }

    public function store(StoreAttributeRequest $request)
    {
        $slug = $this->uniqueSlug($request->validated('name'));

        $attribute = Attribute::create([
            'name' => $request->validated('name'),
            'slug' => $slug,
        ]);

        return response()->json([
            "status" => "success",
            "message" => "Caractéristique créée avec succès.",
            'data' => $attribute,
        ], 201);
    }

    public function show(Attribute $attribute)
    {
        return response()->json([
            "status" => "success",
            'data' => $attribute->load('values'),
        ], 200);
    }

    public function update(UpdateAttributeRequest $request, Attribute $attribute)
    {
        $data = ['name' => $request->validated('name')];

        if ($attribute->name !== $data['name']) {
            $data['slug'] = $this->uniqueSlug($data['name'], $attribute->id);
        }

        $attribute->update($data);

        return response()->json([
            "status" => "success",
            "message" => "Caractéristique mise à jour avec succès.",
            'data' => $attribute,
        ], 200);
    }

    public function destroy(Attribute $attribute)
    {
        if ($attribute->values()->exists()) {
            return response()->json([
                "status" => "error",
                "message" => "Impossible de supprimer cette caractéristique : elle contient encore des valeurs."
            ], 422);
        }

        $attribute->delete();

        return response()->json([
            "status" => "success",
            "message" => "Caractéristique supprimée avec succès.",
        ], 200);
    }

    public function getAttributeValues($id)
    {
        $attributeValues = AttributeValue::where('attribute_id', $id)->orderBy('created_at', 'desc')->get();
        return response()->json([
            "status" => "success",
            "mesage" => "Liste des valeurs de la caractéristique",
            'data' => $attributeValues,
        ], 200);
    }

    private function uniqueSlug(string $name, ?string $ignoreId = null): string
    {
        $base = Str::slug($name);
        $slug = $base;
        $i = 1;

        while (
            Attribute::where('slug', $slug)
                ->when($ignoreId, fn ($q) => $q->whereKeyNot($ignoreId))
                ->exists()
        ) {
            $slug = "{$base}-{$i}";
            $i++;
        }

        return $slug;
    }
}
