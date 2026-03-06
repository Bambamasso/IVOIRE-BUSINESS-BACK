<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Cart;
use App\Models\CartItem;
use App\Models\Products;
use App\Models\ProductVariant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CartController extends Controller
{
    //
    public function index()
    {
        $userid = auth()->id();
        $cart = Cart::where('user_id', $userid)->with(['items.product.media', 'items.variant.attributValues.attribute'])->first();
        return response()->json([
            "status" => "success",
            "cart" => $cart
        ], 200);

    }

    public function store(Request $request)
    {
        // 1. VALIDATION
        $validator = Validator::make($request->all(), [
            'product_id' => 'required|exists:products,id',
            'product_variant_id' => 'nullable|exists:product_variants,id',
            'quantity' => 'required|integer|min:1',
            'session_id' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }


        $userId = auth()->id();
        $productId = $request->input('product_id');
        $variantId = $request->input('product_variant_id');
        $quantity = $request->input('quantity');


        if ($variantId) {
            $source = ProductVariant::findOrFail($variantId);
            $finalPrice = $source->price ?? Products::find($productId)->price;
            // dd($finalPrice);
        } else {
            $source = Products::findOrFail($productId);
            $finalPrice = $source->price;
        }

        // 4. RÉCUPÉRER OU CRÉER LE PANIER
        $cart = Cart::firstOrCreate(
            $userId
            ? ['user_id' => $userId]  // Si connecté
            : ['session_id' => $request->session_id] // Si non connecté
        );

        // 5. VÉRIFIER SI L'ITEM EXISTE DÉJÀ DANS LE PANIER
        $item = CartItem::where('cart_id', $cart->id)
            ->where('product_id', $productId)
            ->where('product_variant_id', $variantId)
            ->first();

        // 6. CALCULER LA QUANTITÉ TOTALE DEMANDÉE
        $newTotalQty = $item ? ($item->quantity + $quantity) : $quantity;

        // 7. VÉRIFICATION DU STOCK
        if ($source->stock_quantity < $newTotalQty) {
            return response()->json([
                'message' => "Stock insuffisant. Disponible: {$source->stock_quantity}",
                'available_stock' => $source->stock_quantity
            ], 422);
        }


        if ($item) {
            // L'item existe déjà → augmenter la quantité
            $item->increment('quantity', $quantity);
        } else {
            // Nouvel item → créer
            CartItem::create([
                'cart_id' => $cart->id,
                'product_id' => $productId,
                'product_variant_id' => $variantId,
                'quantity' => $quantity,
                'price' => $finalPrice
            ]);
        }

        // 9. RETOURNER LA RÉPONSE AVEC LE PANIER RECHARGÉ
        return response()->json([
            "status" => "success",
            'message' => 'Produit ajouté au panier',
            'cart' => $cart->load(['items.product.media', 'items.variant.attributValues.attribute']),
            'total' => $cart->items->sum(fn($i) => $i->price * $i->quantity)
        ], 201);
    }
    public function show(Cart $cart)
    {
        $cart->load(['items.product.media', 'items.variant.attributValues.attribute']);
        return response()->json([
            "status" => "success",
            "cart" => $cart
        ], 200);
    }
    public function update(Request $request, string $itemId)
    {
        $validator = Validator::make($request->all(), [
            'quantity' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json(['errors' => $validator->errors()], 422);
        }

        $userId = auth()->id();

        $cartItem = CartItem::whereHas('cart', function ($query) use ($userId, $request) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $request->session_id);
            }
        })->findOrFail($itemId);

        // Vérifier le stock
        $source = $cartItem->product_variant_id
            ? ProductVariant::find($cartItem->product_variant_id)
            : Products::find($cartItem->product_id);

        if ($source->stock_quantity < $request->quantity) {
            return response()->json([
                'message' => "Stock insuffisant. Disponible: {$source->stock_quantity}",
                'available_stock' => $source->stock_quantity
            ], 422);
        }

        $cartItem->quantity = $request->quantity;
        $cartItem->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Quantité mise à jour',
            'item' => $cartItem
        ], 200);
    }

    public function destroy(Request $request, $itemId)
    {
        $userId = auth()->id();

        $cartItem = CartItem::whereHas('cart', function ($query) use ($userId, $request) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $request->session_id);
            }
        })->findOrFail($itemId);

        $cartItem->delete();

        return response()->json(
            [
                "status" => "success",
                'message' => 'Article supprimé du panier'
            ],
            200
        );
    }
    public function clear(
        Request $request
    ) {
        $userId = auth()->id();

        $cart = Cart::where(function ($query) use ($userId, $request) {
            if ($userId) {
                $query->where('user_id', $userId);
            } else {
                $query->where('session_id', $request->session_id);
            }
        })->first();
        // return $cart;
        if ($cart) {
            $cart->items()->delete();
        }

        return response()->json([
            "status" => "success",
            'message' => 'Panier vidé'
        ], 200);
    }
public function countCart(){
    $userId= auth()->id();
    $cart = Cart::where('user_id', $userId)->first();
    $totalItems=$cart->items()->count();
    return response()->json([
        "status" => "success",
        "total_items" => $totalItems
    ], 200);
}
}
