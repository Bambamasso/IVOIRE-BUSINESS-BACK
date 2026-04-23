<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCategorieRequest;
use App\Http\Requests\UpdateCategorieRequest;
use App\Models\Categorie;
use Illuminate\Http\Request;

class CategoriesController extends Controller
{
    //
    public function index()
    {
        $pre_page = request()->query('per_page', 4);
        $categories = Categorie::with('parent')->orderBy('created_at', 'desc')->paginate($pre_page);
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }

    public function getCategories()
    {
        $categories = Categorie::with('parent')->orderBy('created_at', 'desc')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }

    public function store(StoreCategorieRequest $request)
    {
        $input = $request->all();

        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filePath = $file->store('categorie_pictures', 'public');
            $input['image'] = $filePath;

        }
        $category = Categorie::create($input);
        return response()->json([
            'status' => 'success',
            'data' => $category
        ], 201);
    }

    public function update(Request $request, UpdateCategorieRequest $updateRequest, Categorie $categorie)
    {
        $input = $updateRequest->all();
        if ($request->hasFile('image')) {
            $file = $request->file('image');
            $filePath = $file->store('categorie_pictures', 'public');
            $input['image'] = $filePath;
            $categorie->update($input);
            return response()->json([
                'status' => 'success',
                'data' => $categorie
            ], 200);
        }
        $categorie->update($input);
        return response()->json([
            'status' => 'success',
            'data' => $categorie
        ], 200);
    }

    public function destroy(Categorie $categorie)
    {
        $categorie->delete();
        return response()->json([
            'status' => 'success',
            'message' => 'Catégorie supprimée avec succès'
        ], 200);
    }
    public function sousCategories(Categorie $categorie)
    {
        $sousCategories = $categorie->children;
        return response()->json([
            'status' => 'success',
            'data' => $sousCategories
        ], 200);

    }
    public function parentCategories()
    {
        $categories = Categorie::whereNull('parent_id')->get();
        return response()->json([
            'status' => 'success',
            'data' => $categories
        ], 200);
    }
}
