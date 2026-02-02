<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Models\Medias;
use App\Models\Products;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function index()
    {
        $products = Products::orderBy("created_at", "desc")->with(['media', 'status', 'categorie'])->get();
        return response()->json([
            "status" => 'success',
            "message" => "Liste des produits",
            "data" => $products
        ], 200);

    }

    public function store(Request $request, StoreProductRequest $storeProductRequest)
    {
        $input = $storeProductRequest->all();
        $input['created_by'] = auth()->user()->id;
        $product = Products::create($input);
        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                $directory = 'products/' . now()->format('Y') . '/' . now()->format('m');
                $path = $file->store($directory, 'public');
                $media = Medias::create([
                    'mediable_id' => $product->id,
                    'mediable_type' => Products::class,
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $path,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                ]);
            }
        }
        return response()->json([
            "status" => "success",
            "message" => "Produit créé avec succès",
            "data" => $product->load(['media', 'status', 'categorie'])
        ], 201);
    }
    public function show(Products $product)
    {
        return response()->json([
            "status" => "success",
            "data" => $product->load(['media', 'status', 'categorie'])
        ], 200);
    }
    public function update(Request $request, Products $product)
    {
        $product->update($request->all());
        return response()->json([
            "status" => "success",
            "message" => "Produit mis à jour avec succès",
            "data" => $product->load(['media', 'status', 'categorie'])
        ], 200);
    }
    public function destroy(Products $product)
    {
        if ($product->media) {
            foreach ($product->media as $media) {
                \Storage::disk('public')->delete($media->file_path);
                $media->delete();
            }
        }
        $product->delete();
        return response()->json([
            "status" => "success",
            "message" => "Produit supprimé avec succès"
        ], 200);
    }

    public function getMedia(Products $product)
    {
        $media = $product->media;
        return response()->json([
            "status" => "success",
            "data" => $media
        ], 200);
    }
    public function AddMedia(Request $request, Products $product)
    {
        // Validation
        // $request->validate([
        //     'files' => 'required|array',
        //     'files.*' => 'required|file|mimes:jpeg,png,jpg,gif,svg,webp|max:10240',
        // ], [
        //     'files.required' => 'Au moins un fichier est requis',
        //     'files.*.mimes' => 'Le fichier doit être une image (jpeg, png, jpg, gif, svg, webp)',
        //     'files.*.max' => 'Le fichier ne doit pas dépasser 10MB',
        // ]);

        $createdMedias = [];

        if ($request->hasFile('files')) {
            foreach ($request->file('files') as $file) {
                try {
                    $directory = 'products/' . now()->format('Y') . '/' . now()->format('m');
                    $path = $file->store($directory, 'public');

                    if (!$path) {
                        return response()->json([
                            "status" => "error",
                            "message" => "Erreur lors du stockage du fichier: " . $file->getClientOriginalName()
                        ], 500);
                    }

                    $media = Medias::create([
                        'mediable_id' => $product->id,
                        'mediable_type' => Products::class,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);

                    $createdMedias[] = $media;
                } catch (\Exception $e) {
                    return response()->json([
                        "status" => "error",
                        "message" => "Erreur lors de l'ajout du média: " . $e->getMessage()
                    ], 500);
                }
            }
        }

        return response()->json([
            "status" => "success",
            "message" => count($createdMedias) . " fichier(s) ajouté(s) avec succès",
            "data" => $createdMedias
        ], 201);

    }

    public function UpdateMedia(Request $request, Products $product, $mediaId)
    {


        $media = $product->media()->where('id', $mediaId)->first();
        if (!$media) {
            return response()->json([
                "status" => "error",
                "message" => "Média non trouvé pour ce produit"
            ], 404);

        }

        try {
            // Supprimer l'ancien fichier
            Storage::disk('public')->delete($media->file_path);

            $file = $request->file('file');
            $directory = 'products/' . now()->format('Y') . '/' . now()->format('m');
            $path = $file->store($directory, 'public');

            if (!$path) {
                return response()->json([
                    "status" => "error",
                    "message" => "Erreur lors du stockage du fichier"
                ], 500);
            }

            $media->update([
                'file_name' => $file->getClientOriginalName(),
                'file_path' => $path,
                'file_type' => $file->getClientMimeType(),
                'file_size' => $file->getSize(),
            ]);

            return response()->json([
                "status" => "success",
                "message" => "Fichier mis à jour avec succès",
                "data" => $media
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                "status" => "error",
                "message" => "Erreur lors de la mise à jour: " . $e->getMessage()
            ], 500);
        }
    }
    public function delteMedia(Products $product, $mediaId)
    {
        $media = $product->media()->where('id', $mediaId)->first();
        if (!$media) {
            return response()->json([
                "status" => "error",
                "message" => "Média non trouvé pour ce produit"
            ], 404);
        }
        ;
        \Storage::disk('public')->delete($media->file_path);
        $media->delete();
        return response()->json([
            "status" => "success",
            "message" => "fichier supprimé avec succès"
        ], 200);
    }
}
