<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtherRquest;
use App\Mail\OrderConfirmed;
use App\Models\Cart;
use App\Models\Municipality;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Products;
use App\Models\ProductVariant;
use App\Models\StatusType;

use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use function App\Helpers\getStatusId;

class OrderController extends Controller
{

    //
    public function index()
    {
        $orders = Order::orderBy('created_at', 'desc')->with('status')->get();
        return response()->json([
            "status" => "success",
            "mesage" => "Liste des commandes",
            'data' => $orders,
        ], 200);
    }
    public function store(OtherRquest $request)
    {
        $input = $request->all();
        $user = auth()->user(); // On essaie de récupérer l'utilisateur via le token
        $userId = $user ? $user->id : null;

        $input['order_number'] = $this->orderNumber();
        $input['user_id'] = $userId; // Sera null pour un invité

        // --- LOGIQUE DU PANIER ---
        $itemsToProcess = [];

        if ($userId) {
            // Cas CONNECTÉ : On récupère le panier en BDD comme avant
            $cart = Cart::where('user_id', $userId)->with('items.product', 'items.variant')->first();
            if (!$cart || $cart->items->isEmpty()) {
                return response()->json(['status' => 'error', 'message' => 'Votre panier est vide.'], 400);
            }

            foreach ($cart->items as $cartItem) {
                $itemsToProcess[] = [
                    'product_id' => $cartItem->product_id,
                    'product_variant_id' => $cartItem->product_variant_id,
                    'quantity' => $cartItem->quantity,
                    'product' => $cartItem->product,
                    'variant' => $cartItem->variant,
                ];
            }
        } else {
            // Cas INVITÉ : On traite les items envoyés directement par le Frontend
            if (!isset($input['items']) || empty($input['items'])) {
                return response()->json(['status' => 'error', 'message' => 'Le panier envoyé est vide.'], 400);
            }

            foreach ($input['items'] as $item) {
                $product = Products::find($item['product_id']);
                $variant = isset($item['product_variant_id']) ? ProductVariant::find($item['product_variant_id']) : null;

                $itemsToProcess[] = [
                    'product_id' => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'] ?? null,
                    'quantity' => $item['quantity'],
                    'product' => $product,
                    'variant' => $variant,
                ];
            }
        }

        // --- CALCULS & VÉRIFICATION STOCK ---
        $subtotal = 0;
        foreach ($itemsToProcess as $item) {
            $source = $item['variant'] ?? $item['product'];

            if (!$source || $source->stock_quantity < $item['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stock insuffisant pour : " . ($item['product']->name ?? 'Produit inconnu')
                ], 400);
            }
            $unitPrice = ($item['variant'] && $item['variant']->price)
                ? $item['variant']->price
                : $item['product']->price;

            $subtotal += $unitPrice * $item['quantity'];
        }

        $municipality = Municipality::find($input['municipality_id']);
        $shippingFee = $municipality->shipping_fee;
        $input['total_amount'] = $subtotal + $shippingFee;

        // --- TRANSACTION ---
        return DB::transaction(function () use ($input, $itemsToProcess, $userId, $shippingFee) {
            try {
                // Statuts
                $input['status_id'] = ($input['payment_method'] === 'online')
                    ? $this->getStatus('awaiting_payment', 'payment')
                    : $this->getStatus('pending', 'order');

                $input['payment_status_id'] = $this->getStatus('unpaid', 'payment');
                $orderData = $input;
                unset($orderData['items']);
                $order = Order::create($orderData);

                foreach ($itemsToProcess as $item) {

                    $source = $item['variant'] ?? $item['product'];
                    $unitPrice = ($item['variant'] && $item['variant']->price)
                        ? $item['variant']->price
                        : $item['product']->price;
                    OrderItem::create([
                        'order_id' => $order->id,
                        'product_id' => $item['product_id'],
                        'product_variant_id' => $item['product_variant_id'],
                        'quantity' => $item['quantity'],
                        'unit_price' => $unitPrice,
                        'total_price' => $unitPrice * $item['quantity'],
                    ]);

                    // Décrémentation
                    $source->decrement('stock_quantity', $item['quantity']);
                }

                // Nettoyage panier BDD si connecté
                if ($userId) {
                    Cart::where('user_id', $userId)->first()->items()->delete();
                    // Envoi email à l'utilisateur connecté
                    Mail::to(auth('api')->user()->email)->queue(new OrderConfirmed($order));
                } else {
                    // Envoi email à l'invité (via l'email du formulaire)
                    Mail::to($input['email'])->queue(new OrderConfirmed($order));
                }

                // Paystack
                if ($input['payment_method'] === 'online') {
                    $paymentResponse = $this->initiatePaystack($input['payment_method'], $order); // Passe l'objet order
                    return response()->json([
                        'status' => 'success',
                        'payment_url' => $paymentResponse['data']['authorization_url'],
                        'order_reference' => $order->order_number
                    ]);
                }

                return response()->json([
                    'status' => 'success',
                    'message' => 'Commande enregistrée avec succès !',
                    'order_id' => $order->id,
                    'order_number' => $order->order_number
                ]);

            } catch (\Exception $e) {
                throw $e; // Redirige vers le catch général
            }
        });
    }


    public function show(Order $order)
    {
        $order->load('orderItems.product', 'orderItems.variant.attributValues', 'status', 'paymentStatus', 'city', 'municipality');
        return response()->json([
            'status' => 'success',
            'data' => $order
        ], 200);
    }

    // public function update()
    // {

    // }

    private function orderNumber()
    {
        $number = Order::count();
        $orderNumber = 'CMD-' . str_pad($number + 1, 3, '0', STR_PAD_LEFT) . '-' . date('Ym') . '-' . strtoupper(Str::random(6));
        return $orderNumber;
    }

    private function playstakePayment($methode, $amount)
    {
        // Logic to integrate with PlayStake payment gateway'){
        if ($methode === 'online') {
            // Simulate a successful payment response from PlayStake
            // return [
            //     'success' => true,
            //     'transaction_id' => Str::uuid(),
            //     'amount' => $amount,
            //     'payment_method' => $methode,
            // ];
        }

    }

    private function getStatus(string $code, string $typeCode)
    {
        $statusTypes = StatusType::where('code', $typeCode)->first();
        if (!$statusTypes) {
            return response()->json([
                "message" => "ce type de status est incorrecte"
            ]);
        }
        $status = $statusTypes->statuses->where('code', $code)->first();

        if (!$status) {
            throw new \Exception("Statut '$code' introuvable pour le type '$typeCode'");
        }

        return $status->id;
    }

    public function userOrders()
    {
        $orders = Order::where('user_id', auth()->id())->with('orderItems.product', 'orderItems.variant', 'status')->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders
            ]
        ], 200);
    }

    /**
     * Valider une commande
     */
    public function validate(Request $request, string $id)
    {
        $order = Order::findOrFail($id);

        // Vérifier que la commande est bien en attente
        $pendingStatusId = $this->getStatus('pending', 'order');
        if ($order->status_id !== $pendingStatusId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seules les commandes en attente peuvent être validées.'
            ], 422);
        }

        return DB::transaction(function () use ($order) {
            $order->update([
                'status_id' => $this->getStatus('validated', 'order'),
                'validated_by' => auth()->id(),
            ]);

            // TODO: envoyer un email de confirmation au client
            // Mail::to($order->email)->queue(new OrderValidated($order));

            return response()->json([
                'status' => 'success',
                'message' => 'Commande validée avec succès.',
                'data' => $order->fresh()->load(['status', 'orderItems.product', 'orderItems.variant'])
            ]);
        });
    }

    /**
     * Rejeter/annuler une commande
     */
    public function reject(Request $request, string $id)
    {
        $validation = validator($request->all(), [
            'cancellation_reason' => 'required|string|max:500',
        ]);
        if ($validation->fails()) {
            return response()->json([
                'status' => 'error',
                'message' => 'La raison d\'annulation est requise et doit être une chaîne de caractères de maximum 500 caractères.',
                'errors' => $validation->errors()
            ], 422);
        }

        $order = Order::findOrFail($id);

        // Vérifier que la commande n'est pas déjà annulée ou livrée
        $cancelledStatusId = $this->getStatus('cancelled', 'order');
        $deliveredStatusId = $this->getStatus('delivered', 'order');

        if (in_array($order->status_id, [$cancelledStatusId, $deliveredStatusId])) {
            return response()->json([
                'status' => 'error',
                'message' => 'Cette commande ne peut plus être annulée.'
            ], 422);
        }

        return DB::transaction(function () use ($order, $request) {

            // Remettre le stock
            foreach ($order->orderItems as $item) {
                $source = $item->variant ?? $item->product;
                if ($source) {
                    $source->increment('stock_quantity', $item->quantity);

                    // Mouvement de stock
                    $this->createStockMovement(
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        type: 'in',
                        stockBefore: $source->stock_quantity - $item->quantity,
                        variantId: $item->product_variant_id,
                    );
                }
            }

            $order->update([
                'status_id' => $this->getStatus('cancelled', 'order'),
                'canceled_by' => auth()->id(),
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            // TODO: envoyer un email d'annulation au client
            // Mail::to($order->email)->queue(new OrderCancelled($order));

            return response()->json([
                'status' => 'success',
                'message' => 'Commande annulée avec succès.',
                'data' => $order->fresh()->load(['status', 'orderItems.product', 'orderItems.variant'])
            ]);
        });
    }

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
     * Marquer une commande comme livrée et payée (paiement à la livraison)
     */
    public function markAsDelivered(string $id)
    {
        $order = Order::findOrFail($id);

        $validatedStatusId = $this->getStatus('validated', 'order');
        if ($order->status_id !== $validatedStatusId) {
            return response()->json([
                'status' => 'error',
                'message' => 'Seules les commandes validées peuvent être marquées comme livrées.'
            ], 422);
        }

        return DB::transaction(function () use ($order) {

            $updateData = [
                'status_id' => $this->getStatus('delivered', 'order'),
            ];

            // Si paiement à la livraison → on marque payé automatiquement
            if ($order->payment_method !== 'online') {
                $updateData['payment_status_id'] = $this->getStatus('paid', 'payment');
            }

            $order->update($updateData);

            return response()->json([
                'status' => 'success',
                'message' => 'Commande marquée comme livrée.',
                'data' => $order->fresh()->load(['status', 'orderItems.product'])
            ]);
        });
    }
    public function getPendingOrders()
    {
        $pendingStatusId = $this->getStatus('pending', 'order');
        $orders = Order::where('status_id', $pendingStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders
            ]
        ], 200);
    }

    public function getvalidatedOrders()
    {
        $validatedStatusId = $this->getStatus('validated', 'order');
        $orders = Order::where('status_id', $validatedStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders
            ]
        ], 200);
    }

    public function getRejectedOrders()
    {
        $cancelledStatusId = $this->getStatus('cancelled', 'order');
        $orders = Order::where('status_id', $cancelledStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders
            ]
        ], 200);
    }

    public function getDeliveredOrders()
    {
        $deliveredStatusId = $this->getStatus('delivered', 'order');
        $orders = Order::where('status_id', $deliveredStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders
            ]
        ], 200);
    }

    public function getCancelledOrders()
    {
        $cancelledStatusId = $this->getStatus('cancelled', 'order');
        $orders = Order::where('status_id', $cancelledStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->get();
        return response()->json([
            'status' => 'success',
            'data' => [
                'orders' => $orders
            ]
        ], 200);
    }
}
