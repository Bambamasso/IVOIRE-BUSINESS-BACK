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
use Illuminate\Support\Facades\Cache;
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

        // --- CALCULS & VÉRIFICATION STOCK (pré-contrôle, prix calculé UNE fois) ---
        $subtotal = 0;
        foreach ($itemsToProcess as $index => $item) {
            $source = $item['variant'] ?? $item['product'];

            if (!$source || $source->stock_quantity < $item['quantity']) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Stock insuffisant pour : " . ($item['product']->title ?? 'Produit inconnu')
                ], 400);
            }

            $unitPrice = ($item['variant'] && $item['variant']->price)
                ? $item['variant']->price
                : ($item['product']->price ?? 0);

            $itemsToProcess[$index]['unit_price'] = $unitPrice;
            $subtotal += $unitPrice * $item['quantity'];
        }

        $municipality = Municipality::find($input['municipality_id']);
        if (!$municipality) {
            return response()->json([
                'status' => 'error',
                'message' => "La commune de livraison est introuvable."
            ], 422);
        }
        $shippingFee = $municipality->shipping_fee ?? 0;
        $totalAmount = $subtotal + $shippingFee;

        if ($input['payment_method'] === 'online') {
            $input['total_amount'] = $totalAmount;
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

        // --- TRANSACTION (paiement à la livraison) ---
        $order = DB::transaction(function () use ($input, $itemsToProcess, $userId, $totalAmount) {
            // Whitelist explicite : aucun champ du body n'est injecté à l'aveugle
            $order = Order::create([
                'order_number'      => $input['order_number'],
                'user_id'           => $userId,
                'first_name'        => $input['first_name'],
                'last_name'         => $input['last_name'],
                'email'             => $input['email'],
                'phone_number'      => $input['phone_number'],
                'address'           => $input['address'],
                'city_id'           => $input['city_id'],
                'municipality_id'   => $input['municipality_id'],
                'payment_method'    => $input['payment_method'],
                'total_amount'      => $totalAmount,
                'status_id'         => $this->getStatus('pending', 'order'),
                'payment_status_id' => $this->getStatus('unpaid', 'payment'),
            ]);

            foreach ($itemsToProcess as $item) {
                // Verrou de ligne : empêche la sur-vente en cas de commandes simultanées
                $source = !empty($item['product_variant_id'])
                    ? ProductVariant::where('id', $item['product_variant_id'])->lockForUpdate()->first()
                    : Products::where('id', $item['product_id'])->lockForUpdate()->first();

                if (!$source || $source->stock_quantity < $item['quantity']) {
                    throw new \Exception(
                        "Stock insuffisant pour : " . ($item['product']->title ?? 'produit inconnu')
                    );
                }

                $unitPrice = $item['unit_price'];
                OrderItem::create([
                    'order_id'           => $order->id,
                    'product_id'         => $item['product_id'],
                    'product_variant_id' => $item['product_variant_id'],
                    'quantity'           => $item['quantity'],
                    'unit_price'         => $unitPrice,
                    'total_price'        => $unitPrice * $item['quantity'],
                ]);

                $stockBefore = $source->stock_quantity;
                $source->decrement('stock_quantity', $item['quantity']);
                $this->checkAndMarkOutOfStock($source, $item['product_id'], $item['product_variant_id']);
                $this->createStockMovement(
                    productId: $item['product_id'],
                    quantity: $item['quantity'],
                    type: 'out',
                    stockBefore: $stockBefore,
                    variantId: $item['product_variant_id'],
                );
            }

            if ($userId) {
                Cart::where('user_id', $userId)->first()?->items()->delete();
            }

            return $order;
        });

        // --- NOTIFICATIONS (hors transaction : un échec d'email ne perd pas la commande) ---
        $this->safeMail(fn() => Mail::to($order->email)->send(new OrderConfirmed($order)));
        $this->sendMailToAdmin($order);

        return response()->json([
            'status' => 'success',
            'message' => 'Commande enregistrée avec succès !',
            'order_id' => $order->id,
            'order_number' => $order->order_number
        ]);
    }

    /**
     * Exécute un envoi d'e-mail en isolant toute erreur : la logique métier
     * (commande, changement de statut…) ne doit jamais échouer à cause du mail.
     */
    private function safeMail(callable $send): void
    {
        try {
            $send();
        } catch (\Throwable $e) {
            \Log::error("Échec d'envoi d'e-mail : " . $e->getMessage());
        }
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

    /**
     * Valider une commande
     */
    public function validate(string $id)
    {
        $pendingStatusId = $this->getStatus('pending', 'order');

        try {
            $order = DB::transaction(function () use ($id, $pendingStatusId) {
                $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();

                if ($order->status_id !== $pendingStatusId) {
                    throw new \DomainException('Seules les commandes en attente peuvent être validées.');
                }

                $order->update([
                    'status_id' => $this->getStatus('validated', 'order'),
                    'validated_by' => auth()->id(),
                ]);

                return $order;
            });
        } catch (\DomainException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        $order->load('status');
        $this->safeMail(fn() => Mail::to($order->email)->send(new OrderStatusNotification($order)));

        return response()->json([
            'status' => 'success',
            'message' => 'Commande validée avec succès.',
            'data' => $order->fresh()->load(['status', 'orderItems.product', 'orderItems.variant'])
        ]);
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

        $cancelledStatusId = $this->getStatus('canceled', 'order');
        $deliveredStatusId = $this->getStatus('delivered', 'order');
        $paidStatusId      = $this->getStatus('paid', 'payment');

        try {
            $order = DB::transaction(function () use ($id, $request, $cancelledStatusId, $deliveredStatusId, $paidStatusId) {
                // Verrou sur la commande
                $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();

                if (in_array($order->status_id, [$cancelledStatusId, $deliveredStatusId], true)) {
                    throw new \DomainException('Cette commande ne peut plus être annulée.');
                }

                // Remettre le stock (avec verrou de ligne sur chaque produit / variante)
                foreach ($order->orderItems as $item) {
                    $source = !empty($item->product_variant_id)
                        ? ProductVariant::where('id', $item->product_variant_id)->lockForUpdate()->first()
                        : Products::where('id', $item->product_id)->lockForUpdate()->first();

                    if (!$source) {
                        continue;
                    }

                    $stockBefore = $source->stock_quantity;
                    $source->increment('stock_quantity', $item->quantity);
                    $this->checkAndMarkBackAvailable($source, $item->product_id);
                    $this->createStockMovement(
                        productId: $item->product_id,
                        quantity: $item->quantity,
                        type: 'in',
                        stockBefore: $stockBefore,
                        variantId: $item->product_variant_id,
                    );
                }

                $updateData = [
                    'status_id' => $cancelledStatusId,
                    'canceled_by' => auth()->id(),
                    'cancellation_reason' => $request->cancellation_reason,
                ];

                // Commande déjà payée (en ligne) → à rembourser
                if ($order->payment_status_id === $paidStatusId) {
                    $updateData['payment_status_id'] = $this->getStatus('refunded', 'payment');
                }

                $order->update($updateData);

                return $order;
            });
        } catch (\DomainException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        $order->load('status');
        $this->safeMail(fn() => Mail::to($order->email)->send(new OrderStatusNotification($order)));

        return response()->json([
            'status' => 'success',
            'message' => 'Commande annulée avec succès.',
            'data' => $order->fresh()->load(['status', 'orderItems.product', 'orderItems.variant'])
        ]);
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
        $validatedStatusId = $this->getStatus('validated', 'order');

        try {
            $order = DB::transaction(function () use ($id, $validatedStatusId) {
                $order = Order::whereKey($id)->lockForUpdate()->firstOrFail();

                if ($order->status_id !== $validatedStatusId) {
                    throw new \DomainException('Seules les commandes validées peuvent être marquées comme livrées.');
                }

                $updateData = ['status_id' => $this->getStatus('delivered', 'order')];

                // Paiement à la livraison → on marque payé automatiquement
                if ($order->payment_method === 'cash_on_delivery') {
                    $updateData['payment_status_id'] = $this->getStatus('paid', 'payment');
                }

                $order->update($updateData);

                return $order;
            });
        } catch (\DomainException $e) {
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 422);
        }

        $order->load('status');
        $this->safeMail(fn() => Mail::to($order->email)->send(new OrderStatusNotification($order)));

        return response()->json([
            'status' => 'success',
            'message' => 'Commande marquée comme livrée.',
            'data' => $order->fresh()->load(['status', 'orderItems.product'])
        ]);
    }
    /**
     * Liste paginée des commandes d'un statut donné.
     * La liste admin n'affiche pas le détail des lignes de commande :
     * on ne charge donc que la relation "status" pour éviter un eager loading inutile.
     */
    private function paginatedOrdersByStatus(string $code)
    {
        $perPage = (int) request()->query('per_page', 15);
        $statusId = $this->getStatus($code, 'order');

        return Order::where('status_id', $statusId)
            ->with('status')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPendingOrders()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->paginatedOrdersByStatus('pending'),
        ], 200);
    }

    public function getvalidatedOrders()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->paginatedOrdersByStatus('validated'),
        ], 200);
    }


    public function getDeliveredOrders()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->paginatedOrdersByStatus('delivered'),
        ], 200);
    }

    public function getCancelledOrders()
    {
        return response()->json([
            'status' => 'success',
            'data' => $this->paginatedOrdersByStatus('canceled'),
        ], 200);
    }

    /**
     * Renvoie en une seule requête le nombre de commandes par statut,
     * pour éviter les 4 appels séquentiels du front.
     */
    public function countOrders()
    {
        $codes = ['pending', 'validated', 'delivered', 'canceled'];

        $idToCode = [];
        foreach ($codes as $code) {
            $idToCode[$this->getStatus($code, 'order')] = $code;
        }

        $totals = Order::whereIn('status_id', array_keys($idToCode))
            ->selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $data = [];
        foreach ($idToCode as $id => $code) {
            $data[$code] = (int) ($totals[$id] ?? 0);
        }

        return response()->json([
            'status' => 'success',
            'data' => $data,
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
            $reference = $paymentDetails->data->reference ?? null;

            // IDEMPOTENCE — Paystack rappelle souvent le callback plusieurs fois.
            // Si la commande existe déjà pour cette référence, on ne recrée rien.
            $existingOrder = $reference
                ? Order::where('paystack_reference', $reference)->first()
                : null;
            if ($existingOrder) {
                return redirect($frontendUrl . '/order-success?ref=' . $existingOrder->order_number);
            }

            try {
                $order = DB::transaction(function () use ($metadata, $items, $userId, $reference) {

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
                        'paystack_reference' => $reference,
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

                    return $order;
                });

                // Notifications hors transaction (un échec d'email ne perd pas la commande payée)
                $this->safeMail(fn() => Mail::to($order->email)->send(new OrderConfirmed($order)));
                $this->sendMailToAdmin($order);

                return redirect($frontendUrl . '/order-success?ref=' . $order->order_number);

            } catch (\Throwable $e) {
                // Le client a payé mais la commande n'a pas pu être créée → alerte forte
                \Log::critical(
                    "Paiement Paystack réussi (ref: {$reference}) mais échec de création de commande : "
                        . $e->getMessage()
                );
                return redirect($frontendUrl . '/order-failed?reason=' . urlencode($e->getMessage()));
            }
        }

        return redirect($frontendUrl . '/order-failed');
    }

    private function sendMailToAdmin($order)
    {
        $adminEmails = User::role('admin')->pluck('email')->filter()->all();

        if (empty($adminEmails)) {
            \Log::error("Impossible d'envoyer le mail admin : aucun utilisateur avec le rôle 'admin'.");
            return;
        }

        $this->safeMail(fn() => Mail::to($adminEmails)->send(new AdminOrderNotification($order)));
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

            return;
        }

        // Pas épuisé, mais peut-être proche du seuil → alerte
        $this->notifyLowStock($source, $productId);
    }

    private function checkAndMarkBackAvailable($source, string $productId): void
    {
        if ($source->stock_quantity <= 0) {
            return;
        }

        $availableStatusId = $this->getStatus('available', 'product');
        $outOfStockStatusId = $this->getStatus('out-of-stock', 'product');

        // On ne "réveille" que ce qui était en rupture — pas un produit
        // archivé ou annulé volontairement par un administrateur.
        if ($source->status_id === $outOfStockStatusId) {
            $source->update(['status_id' => $availableStatusId]);
        }

        // Si c'est une variante, repasser aussi le produit parent en disponible
        // (uniquement s'il était lui-même marqué en rupture).
        if ($source instanceof ProductVariant) {
            Products::where('id', $productId)
                ->where('status_id', $outOfStockStatusId)
                ->update(['status_id' => $availableStatusId]);
        }
    }

    /**
     * Journalise et notifie l'admin quand un stock passe sous le seuil d'alerte
     * sans être totalement épuisé.
     */
    private function notifyLowStock($source, string $productId): void
    {
        $threshold = (int) config('shop.low_stock_threshold', 5);
        if ($threshold <= 0) {
            return;
        }

        $stock = (int) $source->stock_quantity;
        if ($stock <= 0 || $stock > $threshold) {
            return;
        }

        $product = $source instanceof ProductVariant ? Products::find($productId) : $source;
        $label = $product->title ?? 'Produit';
        if ($source instanceof ProductVariant) {
            $label .= ' — variante ' . ($source->sku ?? $source->id);
        }

        \Log::warning("Stock bas : {$label} — il reste {$stock} unité(s) (seuil : {$threshold}).");

        try {
            $admin = User::role('admin')->first();
            if ($admin) {
                Mail::raw(
                    "Le stock de \"{$label}\" est bas : il reste {$stock} unité(s) "
                        . "(seuil d'alerte : {$threshold}). Pensez à réapprovisionner.",
                    fn($m) => $m->to($admin->email)->subject("Alerte stock bas — {$label}")
                );
            }
        } catch (\Throwable $e) {
            \Log::error("Échec de l'envoi de l'alerte de stock bas : " . $e->getMessage());
        }
    }

   
}

