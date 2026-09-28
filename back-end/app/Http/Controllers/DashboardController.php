<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Products;
use App\Models\ServiceRequests;
use App\Models\StatusType;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class DashboardController extends Controller
{
    public function index()
    {
        $paidStatusId = $this->getStatus('paid', 'payment');
        $pendingServiceStatusId = $this->getStatus('pending', 'service');
        $outOfStockStatusId = $this->getStatus('out-of-stock', 'product');

        $stats = [
            'products' => [
                'total' => Products::count(),
                'out_of_stock' => Products::where('status_id', $outOfStockStatusId)->count(),
            ],
            'orders' => [
                'total' => Order::count(),
                'revenue' => (float) Order::where('payment_status_id', $paidStatusId)->sum('total_amount'),
            ],
            'services' => [
                'pending' => ServiceRequests::where('status_id', $pendingServiceStatusId)->count(),
            ],
        ];

        $recentOrders = Order::with('status')
            ->latest()
            ->limit(5)
            ->get(['id', 'order_number', 'first_name', 'last_name', 'total_amount', 'status_id', 'created_at']);

        $recentServiceRequests = ServiceRequests::with('status', 'service')
            ->latest()
            ->limit(5)
            ->get(['id', 'request_number', 'full_name', 'service_id', 'status_id', 'created_at']);

        return response()->json([
            'status' => 'success',
            'data' => [
                'stats' => $stats,
                'recent_orders' => $recentOrders,
                'recent_service_requests' => $recentServiceRequests,
            ],
        ], 200);
    }

    private function getStatus(string $code, string $typeCode)
    {
        return Cache::remember("status_id:{$typeCode}:{$code}", now()->addHours(24), function () use ($code, $typeCode) {
            $aliases = [
                'order' => ['canceled' => 'cancelled'],
                'service' => ['rejected' => 'cancelled', 'canceled' => 'cancelled'],
                'product' => ['cancelled' => 'canceled'],
            ];
            $code = $aliases[$typeCode][$code] ?? $code;

            $statusType = StatusType::where('code', $typeCode)->first();
            if (!$statusType) {
                throw new \Exception("Type de statut '$typeCode' introuvable");
            }
            $status = $statusType->statuses->where('code', $code)->first();

            if (!$status) {
                throw new \Exception("Statut '$code' introuvable pour le type '$typeCode'");
            }

            return $status->id;
        });
    }
}
