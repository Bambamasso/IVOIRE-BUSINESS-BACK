<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\OtherRquest;
use App\Mail\AdminOrderNotification;
use App\Mail\OrderConfirmed;
use App\Mail\OrderStatusNotification;
use App\Models\Cart;
use App\Models\Municipality;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Products;
use App\Models\ProductVariant;
use App\Models\StatusType;

use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Unicodeveloper\Paystack\Facades\Paystack;
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
        $user = auth()->user();
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
        if ($input['payment_method'] === 'online') {
            $paymentResponse = $this->initiatePaystack($input, $itemsToProcess);


            if (!$paymentResponse || !isset($paymentResponse['data']['authorization_url'])) {
                throw new \Exception("Impossible de générer le lien de paiement.");
            }
            return response()->json([
                'status' => 'success',
                'payment_url' => $paymentResponse['data']['authorization_url'],
                'reference' => $paymentResponse['data']['reference'],
            ]);
        }
        // --- TRANSACTION ---
        return DB::transaction(function () use ($input, $itemsToProcess, $userId, ) {
            try {

                $input['payment_status_id'] = $this->getStatus('unpaid', 'payment');
                $input['status_id'] = $this->getStatus('pending', 'payment');
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
                    $stockBefore = $source->stock_quantity;
                    $source->decrement('stock_quantity', $item['quantity']);
                    $this->checkAndMarkOutOfStock($source, $item['product_id'], $item['product_variant_id']);
                    // Mouvement de stock (sortie)
                    $this->createStockMovement(
                        productId: $item['product_id'],
                        quantity: $item['quantity'],
                        type: 'out',
                        stockBefore: $stockBefore,
                        variantId: $item['product_variant_id'],
                    );
                }


                if ($userId) {
                    Cart::where('user_id', $userId)->first()->items()->delete();
                    // Envoi email à l'utilisateur connecté
                    Mail::to(auth('api')->user()->email)->queue(new OrderConfirmed($order));
                } else {
                    // Envoi email à l'invité (via l'email du formulaire)
                    Mail::to($input['email'])->queue(new OrderConfirmed($order));
                }

                // Envoi email à l'admin
                $this->sendMailToAdmin($order);

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

    private function orderNumber()
    {
        $number = Order::count();
        $orderNumber = 'CMD-' . str_pad($number + 1, 3, '0', STR_PAD_LEFT) . '-' . date('Ym') . '-' . strtoupper(Str::random(6));
        return $orderNumber;
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

    /**
     * Valider une commande
     */
    public function validate(string $id)
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
            Mail::to($order->email)->queue(new OrderStatusNotification($order));

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
    public function canceled(Request $request, string $id)
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
        $cancelledStatusId = $this->getStatus('canceled', 'order');
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
                    $stockBefore = $source->stock_quantity; // Capturer AVANT
                    $source->increment('stock_quantity', $item->quantity);
                    $this->checkAndMarkBackAvailable($source, $item->product_id);
                    // Mouvement de stock
                    $this->createStockMovement(
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        type: 'in',
                        stockBefore: $stockBefore,
                        variantId: $item->product_variant_id,
                    );
                }
            }

            $order->update([
                'status_id' => $this->getStatus('canceled', 'order'),
                'canceled_by' => auth()->id(),
                'cancellation_reason' => $request->cancellation_reason,
            ]);

            // TODO: envoyer un email d'annulation au client
            Mail::to($order->email)->queue(new OrderStatusNotification($order));

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
        $pre_page = request()->query('per_page', 4);
        $pendingStatusId = $this->getStatus('pending', 'order');
        $orders = Order::where('status_id', $pendingStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->paginate($pre_page);
        return response()->json([
            'status' => 'success',
            'data' => $orders
        ], 200);
    }

    public function getvalidatedOrders()
    {
        $pre_page = request()->query('per_page', 4);
        $validatedStatusId = $this->getStatus('validated', 'order');
        $orders = Order::where('status_id', $validatedStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->paginate($pre_page);
        return response()->json([
            'status' => 'success',
            'data' => $orders

        ], 200);
    }


    public function getDeliveredOrders()
    {
        $pre_page = request()->query('per_page', 4);
        $deliveredStatusId = $this->getStatus('delivered', 'order');
        $orders = Order::where('status_id', $deliveredStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->paginate($pre_page);
        return response()->json([
            'status' => 'success',
            'data' => $orders

        ], 200);
    }

    public function getCancelledOrders()
    {
        $pre_page = request()->query('per_page', 4);
        $cancelledStatusId = $this->getStatus('canceled', 'order');
        $orders = Order::where('status_id', $cancelledStatusId)->with('orderItems.product', 'orderItems.variant', 'status')->paginate($pre_page);
        return response()->json([
            'status' => 'success',
            'data' => $orders

        ], 200);
    }

    private function initiatePaystack(array $input, array $itemsToProcess)
    {
        // Paystack travaille en sous (centimes), donc on multiplie par 100 pour le XOF
        $itemsForMeta = array_map(function ($item) {
            $unitPrice = ($item['variant'] && $item['variant']->price)
                ? $item['variant']->price
                : $item['product']->price;

            return [
                'product_id' => $item['product_id'],
                'product_variant_id' => $item['product_variant_id'],
                'quantity' => $item['quantity'],
                'unit_price' => $unitPrice,
            ];
        }, $itemsToProcess);

        $data = [
            "amount" => $input['total_amount'] * 100, // En centimes
            "reference" => Paystack::genTranxRef(),
            "email" => $input['email'],
            "currency" => "XOF",
            "callback_url" => route('payment.callback'),
            "metadata" => [
                "first_name" => $input['first_name'],
                "last_name" => $input['last_name'],
                "email" => $input['email'],
                "phone_number" => $input['phone_number'],
                "address" => $input['address'],
                "city_id" => $input['city_id'],
                "municipality_id" => $input['municipality_id'],
                "total_amount" => $input['total_amount'],
                "user_id" => $input['user_id'] ?? null,
                "items" => json_encode($itemsForMeta),
            ],
        ];

        try {
            // Cette méthode du package Unicodeveloper retourne l'objet de réponse de Paystack
            return Paystack::getAuthorizationResponse($data);

        } catch (\Exception $e) {
            throw new \Exception("Erreur lors de l'initialisation de Paystack : " . $e->getMessage());
        }
    }
    public function handleGatewayCallback()
    {
        $paymentDetails = Paystack::getPaymentData();
        $frontendUrl = env('FRONTEND_URL', 'http://localhost:3000');
        $metadata = isset($paymentDetails->data->metadata) ? (array) $paymentDetails->data->metadata : [];

        if (
            isset($paymentDetails->status, $paymentDetails->data->status)
            && $paymentDetails->status === true && $paymentDetails->data->status === 'success'
        ) {

            $items = isset($metadata['items']) ? json_decode($metadata['items'], true) : [];
            $userId = $metadata['user_id'] ?? null;

            try {
                $order = DB::transaction(function () use ($metadata, $items, $userId, $paymentDetails) {

                    // CORRECTION 1 — Une seule boucle de vérification avec lock,
                    // on sauvegarde chaque $source dans un tableau
                    $lockedSources = [];

                    foreach ($items as $item) {
                        $source = !empty($item['product_variant_id'])
                            ? ProductVariant::where('id', $item['product_variant_id'])->lockForUpdate()->first()
                            : Products::where('id', $item['product_id'])->lockForUpdate()->first();

                        if (!$source || $source->stock_quantity < $item['quantity']) {
                            throw new \Exception('Stock insuffisant pour un des produits.');
                        }

                        // On mémorise le $source locké avec l'id comme clé
                        $sourceKey = $item['product_variant_id'] ?? $item['product_id'];
                        $lockedSources[$sourceKey] = $source;
                    }

                    // --- Création de la commande ---
                    $order = Order::create([
                        'order_number' => $this->orderNumber(),
                        'user_id' => $userId,
                        'first_name' => $metadata['first_name'] ?? null,
                        'last_name' => $metadata['last_name'] ?? null,
                        'email' => $metadata['email'] ?? null,
                        'phone_number' => $metadata['phone_number'] ?? null,
                        'address' => $metadata['address'] ?? null,
                        'city_id' => $metadata['city_id'] ?? null,
                        'municipality_id' => $metadata['municipality_id'] ?? null,
                        'total_amount' => $metadata['total_amount'] ?? null,
                        'payment_method' => 'online',
                        'status_id' => $this->getStatus('validated', 'order'),
                        'payment_status_id' => $this->getStatus('paid', 'payment'),
                        'paystack_reference' => $paymentDetails->data->reference ?? null,
                    ]);

                    // --- Création des items + décrémentation stock ---
                    foreach ($items as $item) {
                        OrderItem::create([
                            'order_id' => $order->id,
                            'product_id' => $item['product_id'],
                            'product_variant_id' => $item['product_variant_id'],
                            'quantity' => $item['quantity'],
                            'unit_price' => $item['unit_price'],
                            'total_price' => $item['unit_price'] * $item['quantity'],
                        ]);

                        // CORRECTION 2 — On réutilise le $source déjà locké,
                        // pas de nouveau find() qui perdrait le lock
                        $sourceKey = $item['product_variant_id'] ?? $item['product_id'];
                        $source = $lockedSources[$sourceKey];

                        $stockBefore = $source->stock_quantity;
                        $source->decrement('stock_quantity', $item['quantity']);

                        $this->createStockMovement(
                            productId: $item['product_id'],
                            quantity: $item['quantity'],
                            type: 'out',
                            stockBefore: $stockBefore,
                            variantId: $item['product_variant_id'],
                        );

                        // CORRECTION 3 — Vérifier et mettre en rupture si stock = 0
                        $this->checkAndMarkOutOfStock(
                            $source,
                            $item['product_id'],
                            $item['product_variant_id'] ?? null
                        );
                    }

                    // Nettoyage panier BDD si connecté
                    if ($userId) {
                        Cart::where('user_id', $userId)->first()?->items()->delete();
                    }

                    Mail::to($metadata['email'])->queue(new OrderConfirmed($order));

                    // Envoi email à l'admin
                    $this->sendMailToAdmin($order);
                    return $order;
                });

                return redirect($frontendUrl . '/order-success?ref=' . $order->order_number);

            } catch (\Exception $e) {
                return redirect($frontendUrl . '/order-failed?reason=' . urlencode($e->getMessage()));
            }
        }

        return redirect($frontendUrl . '/order-failed');
    }

    private function sendMailToAdmin($order)
    {
        // On récupère le premier admin
        $admin = User::role('admin')->first();

        // Sécurité : On vérifie qu'un admin existe bien en BDD
        if (!$admin) {
            \Log::error("Impossible d'envoyer le mail admin : aucun utilisateur avec le rôle 'admin' n'a été trouvé.");
            return;
        }

        try {
            Mail::to($admin->email)->queue(new AdminOrderNotification($order));
        } catch (\Exception $e) {
            // On log l'erreur dans storage/logs/laravel.log pour pouvoir la lire sans bloquer l'application
            \Log::error("Échec de l'envoi du mail à l'admin (" . $admin->email . ") : " . $e->getMessage());
        }
    }

    private function checkAndMarkOutOfStock($source, string $productId, ?string $variantId = null): void
    {
        $source->refresh(); // S'assurer d'avoir la valeur à jour après decrement

        if ($source->stock_quantity <= 0) {
            $outOfStockStatusId = $this->getStatus('out-of-stock', 'product');

            if ($variantId) {
                // Mettre la variante en rupture
                $source->update(['status_id' => $outOfStockStatusId]);

                // Vérifier si TOUTES les variantes du produit sont en rupture
                $product = Products::find($productId);
                $allOutOfStock = $product->variants()
                    ->where('status_id', '!=', $outOfStockStatusId)
                    ->doesntExist();

                if ($allOutOfStock) {
                    $product->update(['status_id' => $outOfStockStatusId]);
                }
            } else {
                // Produit sans variante → passer directement le produit en rupture
                Products::where('id', $productId)
                    ->update(['status_id' => $outOfStockStatusId]);
            }
        }
    }

    private function checkAndMarkBackAvailable($source, string $productId): void
    {
        if ($source->stock_quantity > 0) {
            $availableStatusId = $this->getStatus('available', 'product');
            $source->update(['status_id' => $availableStatusId]);

            // Si c'est une variante, repasser aussi le produit parent en disponible
            if ($source instanceof ProductVariant) {
                Products::where('id', $productId)
                    ->update(['status_id' => $availableStatusId]);
            }
        }
    }

   
}

