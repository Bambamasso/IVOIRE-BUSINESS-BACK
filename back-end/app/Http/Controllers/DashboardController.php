<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Products;
use App\Models\ServiceRequests;
use App\Models\StatusType;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    //
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $this->getStats(),
                // 'orders_chart'       => $this->getOrdersPerMonth(),
                // 'orders_vs_services' => $this->getOrdersVsServices(),
                // 'orders_by_status'   => $this->getOrdersByStatus(),
            ]
        ], 200);
    }

    private function getStats(): array
    {
        $now = now();

        // Produits
        $totalProducts = Products::count();
        // $outOfStockId = $this->getStatusId('out-of-stock', 'product');
        // $outOfStock = Products::where('status_id', $outOfStockId)->count();

        // Commandes
        $totalOrders = Order::count();
        $ordersThisMonth = Order::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // CA du mois — somme des commandes livrées ou confirmées ce mois
        // $deliveredId = $this->getStatusId('delivered', 'order');
        // $confirmedId = $this->getStatusId('confirmed', 'order');
        // $revenueThisMonth = Order::whereIn('status_id', array_filter([$deliveredId, $confirmedId]))
        //     ->whereMonth('created_at', $now->month)
        //     ->whereYear('created_at', $now->year)
        //     ->sum('total_amount');

        // Demandes de service
        $totalServices = ServiceRequests::count();
        $servicesThisMonth = ServiceRequests::whereMonth('created_at', $now->month)
            ->whereYear('created_at', $now->year)
            ->count();

        // Demandes en attente
        // $pendingOrderId = $this->getStatusId('pending', 'order');
        // $pendingOrders = Order::where('status_id', $pendingOrderId)->count();

        // $pendingServiceId = $this->getStatusId('pending', 'service');
        // $pendingServices = ServiceRequests::where('status_id', $pendingServiceId)->count();

        return [
            'products' => [
                'total' => $totalProducts,
                // 'out_of_stock' => $outOfStock,
            ],
            'orders' => [
                'total' => $totalOrders,
                'this_month' => $ordersThisMonth,
                // 'pending' => $pendingOrders,
            ],
            // 'revenue' => [
            //     'this_month' => $revenueThisMonth,
            // ],
            'services' => [
                'total' => $totalServices,
                'this_month' => $servicesThisMonth,
                // 'pending' => $pendingServices,
            ],
        ];
    }
    private function getStatusId(string $code, string $typeCode): ?string
    {
        $type = StatusType::where('code', $typeCode)->first();
        if (!$type)
            return null;

        $status = $type->statuses->where('code', $code)->first();
        return $status?->id;
    }
}
