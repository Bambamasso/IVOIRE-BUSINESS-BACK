<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreProductRequest;
use App\Http\Requests\UpdateProductRequest;
use App\Models\Medias;
use App\Models\Products;
use App\Models\ProductVariant;
use App\Models\StatusType;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function index()
    {
        $per_page = request()->query('per_page', 4);
        $products = Products::orderBy("created_at", "desc")->with(['media', 'status', 'categorie', 'variants', 'variants.attributValues'])->paginate($per_page);
        return response()->json([
            "status" => 'success',
            "message" => "Liste des produits",
            "data" => $products
        ], 200);

    }

    public function allProducts()
    {

        $products = Products::orderBy('created_at', 'desc')->with(['media', 'status', 'categorie', 'variants', 'variants.attributValues'])->get();
        return response()->json([
            "status" => 'success',
            "message" => "Liste des produits",
            "data" => $products
        ], 200);
    }

    public function store(Request $request, StoreProductRequest $storeProductRequest)
    {
        $input = $storeProductRequest->all();
        $input['created_by'] = auth()->id();
        $input['status_id'] = $this->getStatus('available', 'product');
        return DB::transaction(function () use ($request, $input) {

            $product = Products::create($input);

            // Médias
            if ($request->hasFile('files')) {
                foreach ($request->file('files') as $file) {
                    $directory = 'products/' . now()->format('m');
                    $path = $file->store($directory, 'public');
                    Medias::create([
                        'mediable_id' => $product->id,
                        'mediable_type' => Products::class,
                        'file_name' => $file->getClientOriginalName(),
                        'file_path' => $path,
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                    ]);
                }
            }

            // Variantes
            if (!empty($input['variantes'])) {
                foreach ($input['variantes'] as $variante) {

                    $createdVariante = ProductVariant::create([
                        'product_id' => $product->id,
                        'status_id' => $this->getStatus('available', 'product'),
                        'stock_quantity' => $variante['stock_quantity'] ?? 0,
                        'sku' => $variante['sku'] ?? null,
                        'price' => $variante['price'] ?? null,
                        'created_by' => auth()->id(),
                    ]);

                    // Attributs
                    if (!empty($variante['attribute_values'])) {
                        $createdVariante->attributValues()->attach($variante['attribute_values']);
                    }

                    // Mouvement de stock initial
                    if ($createdVariante->stock_quantity > 0) {
                        $this->createStockMovement(
                            productId: $product->id,
                            quantity: $createdVariante->stock_quantity,
                            type: 'in',
                            stockBefore: 0,
                            variantId: $createdVariante->id,
                        );
                    }
                }
            } else {
                // Produit sans variante — mouvement sur le produit directement
                if ($product->stock_quantity > 0) {
                    $this->createStockMovement(
                        productId: $product->id,
                        quantity: $product->stock_quantity,
                        type: 'in',
                        stockBefore: 0,
                    );
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Produit créé avec succès',
                'data' => $product->load(['media', 'status', 'categorie']),
            ], 201);
        });
    }
    public function show(Products $product)
    {
        return response()->json([
            "status" => "success",
            "data" => $product->load(['media', 'status', 'categorie', 'variants.status', 'variants.attributValues.attribute'])
        ], 200);
    }
    public function update(UpdateProductRequest $updateProductRequest, Products $product)
    {
        $input = $updateProductRequest->all();
        $input['updated_by'] = auth()->id();

        return DB::transaction(function () use ($input, $product, $updateProductRequest) {
            $product->update($input);

            if ($updateProductRequest->has('variantes')) {
                $incomingVariantes = $updateProductRequest->input('variantes', []);
                $incomingIds = collect($incomingVariantes)->pluck('id')->filter()->toArray();

                $variantesToDelete = $product->variants()
                    ->whereNotIn('id', $incomingIds)
                    ->get();

                foreach ($variantesToDelete as $variant) {
                    $variant->attributValues()->detach();
                    $variant->delete();
                }

                foreach ($incomingVariantes as $varianteData) {
                    if (!empty($varianteData['id'])) {
                        $variant = ProductVariant::find($varianteData['id']);

                        if ($variant && $variant->product_id === $product->id) {
                            $oldStock = $variant->stock_quantity;
                            $newStock = $varianteData['stock_quantity'] ?? $oldStock;

                            $variant->update([
                                'price' => $varianteData['price'] ?? $variant->price,
                                'sku' => $varianteData['sku'] ?? $variant->sku,
                                'stock_quantity' => $newStock,
                                'status_id' => $varianteData['status_id'] ?? $variant->status_id,
                                'updated_by' => auth()->id(),
                            ]);

                            if ($newStock !== $oldStock) {
                                $diff = abs($newStock - $oldStock);
                                $this->createStockMovement(
                                    productId: $product->id,
                                    quantity: $diff,
                                    type: $newStock > $oldStock ? 'in' : 'out',
                                    stockBefore: $oldStock,
                                    variantId: $variant->id,
                                );
                            }

                            if (isset($varianteData['attribute_values'])) {
                                $variant->attributValues()->sync($varianteData['attribute_values']);
                            }
                        }
                    } else {
                        $newVariant = ProductVariant::create([
                            'product_id' => $product->id,
                            'status_id' => $this->getStatus('available', 'product'),
                            'stock_quantity' => $varianteData['stock_quantity'] ?? 0,
                            'price' => $varianteData['price'] ?? null,
                            'created_by' => auth()->id(),
                        ]);

                        if (!empty($varianteData['attribute_values'])) {
                            $newVariant->attributValues()->attach($varianteData['attribute_values']);
                        }

                        if ($newVariant->stock_quantity > 0) {
                            $this->createStockMovement(
                                productId: $product->id,
                                quantity: $newVariant->stock_quantity,
                                type: 'in',
                                stockBefore: 0,
                                variantId: $newVariant->id,
                            );
                        }
                    }
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Produit mis à jour avec succès',
                'data' => $product->load([
                    'media',
                    'status',
                    'categorie',
                    'variants',
                    'variants.attributValues.attribute'
                ]),
            ], 200);
        });
    }

    public function destroy(Products $product)
    {

        $variants = $product->variants;
        foreach ($variants as $variant) {
            $variant->attributValues()->detach();
            $variant->delete();
        }
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

    /**
     * Enregistre un mouvement de stock
     */
    private function createStockMovement(
        string $productId,
        int $quantity,
        string $type,
        int $stockBefore,
        ?string $variantId = null
    ): void {
        StockMovement::create([
            'product_id' => $productId,
            'variant_id' => $variantId,
            'quantity' => $quantity,
            'type' => $type,
            'stock_before' => $stockBefore,
            'stock_after' => $type === 'in'
                ? $stockBefore + $quantity
                : $stockBefore - $quantity,
            'created_by' => auth()->id(),
        ]);
    }

    private function getStatus(string $code, string $typeCode)
    {
        $statusTypes = StatusType::where('code', $typeCode)->first();
        if (!$statusTypes) {
            throw new \Exception("Type de statut '$typeCode' introuvable");
        }
        $status = $statusTypes->statuses->where('code', $code)->first();

        if (!$status) {
            throw new \Exception("Statut '$code' introuvable pour le type '$typeCode'");
        }

        return $status->id;
    }

    public function getAvailableProducts()
    {
        $pre_page = request()->query('per_page', 4);
        $activeStatusId = $this->getStatus('available', 'product');
        $products = Products::where('status_id', $activeStatusId)->with(['media', 'status', 'categorie', 'variants', 'variants.attributValues.attribute'])->paginate($pre_page);

        return response()->json([
            "status" => "success",
            "data" => $products
        ], 200);
    }

    public function getOutOfProducts()
    {
        $pre_page = request()->query('per_page', 4);
        $statusId = $this->getStatus('out-of-stock', 'product');
        $products = Products::where('status_id', $statusId)->with(['media', 'status', 'categorie', 'variants', 'variants.attributValues.attribute'])->paginate($pre_page);
        return response()->json([
            "status" => "success",
            "data" => $products
        ], 200);
    }

    public function getProdunctsByCategory($categoryId)
    {

        $products = Products::where('category_id', $categoryId)->with(['media', 'status', 'categorie', 'variants', 'variants.attributValues.attribute'])->get();
        return response()->json([
            "status" => "success",
            "data" => $products
        ], 200);
    }

    public function similarProducts(Products $product)
    {
        $similarProducts = Products::where('category_id', $product->category_id)
            ->where('id', '!=', $product->id)
            ->with(['media', 'status', 'categorie', 'variants', 'variants.attributValues.attribute'])
            ->limit(4)
            ->get();

        return response()->json([
            "status" => "success",
            "data" => $similarProducts
        ], 200);

    }
   
}
