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
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    //
    public function index()
    {
        $per_page = request()->query('per_page', 15);
        $products = Products::orderBy("created_at", "desc")
            ->with(['categorie:id,name', 'variants:id,product_id,stock_quantity'])
            ->paginate($per_page);
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
            $oldProductStock = $product->stock_quantity;

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

                // Recalcul des statuts (rupture <-> actif) après réappro des variantes
                $product->load('variants');
                foreach ($product->variants as $pv) {
                    $this->syncVariantStockStatus($pv);
                }
                $this->syncProductStockStatus($product);
            } else {
                // Produit sans variante : tracer le mouvement + recalculer le statut
                $product->refresh();
                $newProductStock = $product->stock_quantity;

                if ($updateProductRequest->has('stock_quantity')
                    && (int) $newProductStock !== (int) $oldProductStock) {
                    $this->createStockMovement(
                        productId: $product->id,
                        quantity: abs((int) $newProductStock - (int) $oldProductStock),
                        type: $newProductStock > $oldProductStock ? 'in' : 'out',
                        stockBefore: (int) $oldProductStock,
                    );
                }

                $this->syncProductStockStatus($product);
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

    /**
     * Statuts "métier" posés volontairement par un admin : on ne les écrase jamais
     * lors d'un recalcul automatique rupture <-> actif.
     */
    private function protectedProductStatusIds(): array
    {
        return [
            $this->getStatus('archived', 'product'),
            $this->getStatus('canceled', 'product'),
        ];
    }

    /**
     * Aligne le statut d'une variante sur son stock (actif si > 0, sinon rupture).
     */
    private function syncVariantStockStatus(ProductVariant $variant): void
    {
        if (in_array($variant->status_id, $this->protectedProductStatusIds(), true)) {
            return;
        }

        $target = (int) $variant->stock_quantity > 0
            ? $this->getStatus('available', 'product')
            : $this->getStatus('out-of-stock', 'product');

        if ($variant->status_id !== $target) {
            $variant->forceFill(['status_id' => $target])->save();
        }
    }

    /**
     * Aligne le statut d'un produit sur son stock : actif s'il reste du stock
     * (sur le produit lui-même ou sur au moins une variante), sinon rupture.
     */
    private function syncProductStockStatus(Products $product): void
    {
        if (in_array($product->status_id, $this->protectedProductStatusIds(), true)) {
            return;
        }

        $product->loadMissing('variants');

        $inStock = $product->variants->isNotEmpty()
            ? $product->variants->contains(fn($v) => (int) $v->stock_quantity > 0)
            : (int) $product->stock_quantity > 0;

        $target = $inStock
            ? $this->getStatus('available', 'product')
            : $this->getStatus('out-of-stock', 'product');

        if ($product->status_id !== $target) {
            $product->forceFill(['status_id' => $target])->save();
        }
    }

    private function getStatus(string $code, string $typeCode)
    {
        return Cache::remember("status_id:{$typeCode}:{$code}", now()->addHours(24), function () use ($code, $typeCode) {
            // Tolérance sur les variantes de nommage réellement présentes en base.
            $aliases = [
                'order' => ['canceled' => 'cancelled'],
                'service' => ['rejected' => 'cancelled', 'canceled' => 'cancelled'],
                'product' => ['cancelled' => 'canceled'],
            ];
            $code = $aliases[$typeCode][$code] ?? $code;

            $statusTypes = StatusType::where('code', $typeCode)->first();
            if (!$statusTypes) {
                throw new \Exception("Type de statut '$typeCode' introuvable");
            }
            $status = $statusTypes->statuses->where('code', $code)->first();

            if (!$status) {
                throw new \Exception("Statut '$code' introuvable pour le type '$typeCode'");
            }

            return $status->id;
        });
    }

    public function getAvailableProducts()
    {
        $pre_page = request()->query('per_page', 15);
        $activeStatusId = $this->getStatus('available', 'product');
        $products = Products::where('status_id', $activeStatusId)
            ->orderBy('created_at', 'desc')
            ->with(['categorie:id,name', 'variants:id,product_id,stock_quantity'])
            ->paginate($pre_page);

        return response()->json([
            "status" => "success",
            "data" => $products
        ], 200);
    }

    public function getOutOfProducts()
    {
        $pre_page = request()->query('per_page', 15);
        $statusId = $this->getStatus('out-of-stock', 'product');
        $products = Products::where('status_id', $statusId)
            ->orderBy('created_at', 'desc')
            ->with(['categorie:id,name', 'variants:id,product_id,stock_quantity'])
            ->paginate($pre_page);
        return response()->json([
            "status" => "success",
            "data" => $products
        ], 200);
    }

    public function countProducts()
    {
        return response()->json([
            "status" => "success",
            "data" => [
                "all" => Products::count(),
                "available" => Products::where('status_id', $this->getStatus('available', 'product'))->count(),
                "out-of-stock" => Products::where('status_id', $this->getStatus('out-of-stock', 'product'))->count(),
            ],
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
