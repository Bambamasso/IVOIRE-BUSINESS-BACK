<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequesterRequest;
use App\Mail\ServiceRequestAdmin;
use App\Mail\ServiceRequestClient;
use App\Mail\ServiceRequestRejected;
use App\Models\ServiceRequests;
use App\Models\Status;
use App\Models\StatusType;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Str;


class ServiceRequestsController extends Controller
{
    public function index(Request $request)
    {
        $perPage = $request->get('per_page', 15);

        $serviceRequests = ServiceRequests::with('service', 'status')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            "status" => "success",
            "message" => "Service requests retrieved successfully",
            "data" => $serviceRequests
        ], 200);
    }

    public function store(StoreServiceRequesterRequest $storeServiceRequest)
    {
        // Whitelist explicite : seuls les champs que le client est autorisé à fournir
        // sont transmis à create() (status_id, final_price, validated_by... restent gérés en interne).
        $input = $storeServiceRequest->validated();
        $serviceRequest = null;

        DB::transaction(function () use (&$input, &$serviceRequest, $storeServiceRequest) {
            $input['status_id'] = $this->getStatus('pending', 'service');
            $input['request_number'] = $this->generateRequestNumber();
            $serviceRequest = ServiceRequests::create($input);

            if ($storeServiceRequest->hasFile('files')) {
                foreach ($storeServiceRequest->file('files') as $file) {
                    $directory = "service_requests";
                    $path = $file->store($directory, 'public');
                    $serviceRequest->media()->create([
                        'file_path' => $path,
                        'file_name' => $file->getClientOriginalName(),
                        'file_type' => $file->getClientMimeType(),
                        'file_size' => $file->getSize(),
                        'type' => 'service_request'
                    ]);
                }


            }

            try {
                Mail::to($input['email'])->queue(new ServiceRequestClient($serviceRequest));
                Mail::to($this->sendMailToAdmin())->queue(new ServiceRequestAdmin($serviceRequest));
            } catch (\Exception $e) {
                Log::error("erreur lors de l'envoie du mail" . $e->getMessage());
            }
        });

        return response()->json([
            "status" => "success",
            "message" => "Votre demande de service a été soumise avec succès. Nous vous contacterons bientôt.",
            "data" => $serviceRequest->load('service', 'status'),
        ], 201);
    }

    public function show(ServiceRequests $request)
    {
        return response()->json([
            "status" => "success",
            "message" => "Service request retrieved successfully",
            "data" => $request->load([
                'service',
                'status',
                'media' => function ($query) {
                    $query->where('type', 'service_request');
                }
            ]),
        ], 200);
    }


    public function validateRequest(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::find($requestId);

        if (!$serviceRequest) {
            return response()->json([
                "status" => "error",
                "message" => "Service request not found"
            ], 404);
        }

        $pendingStatus = $this->getStatus('pending', 'service');

        if ($serviceRequest->status_id !== $pendingStatus) {
            return response()->json([
                "status" => "error",
                "message" => "Seules les demandes en attente peuvent être validées."
            ], 422);
        }

        $validated = validator($request->all(), [
            "final_price" => "required|numeric|min:0",
        ]);

        if ($validated->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Validation error",
                "errors" => $validated->errors()
            ], 422);
        }

        $serviceRequest->final_price = $validated->validated()['final_price'];
        $serviceRequest->status_id = $this->getStatus('validated', 'service');
        $serviceRequest->validated_by = auth()->id();
        $serviceRequest->save();

        return response()->json([
            "status" => "success",
            "message" => "Demande validée avec succès.",
            "data" => $serviceRequest->load('service', 'status'),
        ], 200);
    }

    public function startProcessing(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::find($requestId);

        if (!$serviceRequest) {
            return response()->json([
                "status" => "error",
                "message" => "Service request not found"
            ], 404);
        }

        $validatedStatus = $this->getStatus('validated', 'service');

        if ($serviceRequest->status_id !== $validatedStatus) {
            return response()->json([
                "status" => "error",
                "message" => "Seules les demandes validées peuvent être mises en cours de traitement."
            ], 422);
        }

        $serviceRequest->status_id = $this->getStatus('in-progress', 'service');
        $serviceRequest->save();

        return response()->json([
            "status" => "success",
            "message" => "Demande mise en cours de traitement.",
            "data" => $serviceRequest->load('service', 'status'),
        ], 200);
    }

    public function completeRequest(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::find($requestId);

        if (!$serviceRequest) {
            return response()->json([
                "status" => "error",
                "message" => "Service request not found"
            ], 404);
        }

        $inProgressStatus = $this->getStatus('in-progress', 'service');

        if ($serviceRequest->status_id !== $inProgressStatus) {
            return response()->json([
                "status" => "error",
                "message" => "Seules les demandes en cours de traitement peuvent être marquées comme terminées."
            ], 422);
        }

        $serviceRequest->status_id = $this->getStatus('completed', 'service');
        $serviceRequest->save();

        return response()->json([
            "status" => "success",
            "message" => "Demande marquée comme terminée.",
            "data" => $serviceRequest->load('service', 'status'),
        ], 200);
    }

    public function rejectRequest(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::find($requestId);

        if (!$serviceRequest) {
            return response()->json([
                "status" => "error",
                "message" => "Service request not found"
            ], 404);
        }

        $completedStatus = $this->getStatus('completed', 'service');
        $rejectedStatus = $this->getStatus('rejected', 'service');

        if ($serviceRequest->status_id === $completedStatus || $serviceRequest->status_id === $rejectedStatus) {
            return response()->json([
                "status" => "error",
                "message" => "Cette demande à déjà été traitée ou rejetée"
            ], 422);
        }

        $validatedData = validator($request->all(), [
            "rejection_reason" => "required|string|max:255",
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Validation error",
                "errors" => $validatedData->errors()
            ], 422);
        }

        $serviceRequest->status_id = $rejectedStatus;
        $serviceRequest->rejected_by = auth()->id();
        $serviceRequest->rejection_reason = $validatedData->validated()['rejection_reason'];
        $serviceRequest->save();

        try {
            Mail::to($serviceRequest->email)->send(new ServiceRequestRejected($serviceRequest));
        } catch (\Exception $e) {
            Log::error("erreur lors de l'envoie du mail de rejet" . $e->getMessage());
        }

        return response()->json([
            "status" => "success",
            "message" => "Service request rejected successfully",
            "data" => $serviceRequest->load('service', 'status'),
        ], 200);
    }

    /**
     * Liste paginée des demandes d'un statut donné.
     * La liste admin n'affiche pas le détail du service : on ne charge que "status".
     */
    private function paginatedRequestsByStatus(string $code, Request $request)
    {
        $perPage = (int) $request->get('per_page', 15);

        return ServiceRequests::where('status_id', $this->getStatus($code, 'service'))
            ->with('status')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);
    }

    public function getPendingRequests(Request $request)
    {
        return response()->json([
            "status" => "success",
            "message" => "Pending service requests retrieved successfully",
            "data" => $this->paginatedRequestsByStatus('pending', $request)
        ], 200);
    }

    public function getValidatedRequests(Request $request)
    {
        return response()->json([
            "status" => "success",
            "message" => "Validated service requests retrieved successfully",
            "data" => $this->paginatedRequestsByStatus('validated', $request)
        ], 200);
    }

    public function getInProgressRequests(Request $request)
    {
        return response()->json([
            "status" => "success",
            "message" => "In-progress service requests retrieved successfully",
            "data" => $this->paginatedRequestsByStatus('in-progress', $request)
        ], 200);
    }

    public function getCompletedRequests(Request $request)
    {
        return response()->json([
            "status" => "success",
            "message" => "Completed service requests retrieved successfully",
            "data" => $this->paginatedRequestsByStatus('completed', $request)
        ], 200);
    }

    public function getRejectedRequests(Request $request)
    {
        return response()->json([
            "status" => "success",
            "message" => "Rejected service requests retrieved successfully",
            "data" => $this->paginatedRequestsByStatus('rejected', $request)
        ], 200);
    }

    /**
     * Nombre de demandes par statut en une seule requête,
     * pour éviter les appels multiples du front.
     */
    public function countServiceRequests()
    {
        $codes = ['pending', 'validated', 'in-progress', 'completed', 'rejected'];

        $idToCode = [];
        foreach ($codes as $code) {
            $idToCode[$this->getStatus($code, 'service')] = $code;
        }

        $totals = ServiceRequests::whereIn('status_id', array_keys($idToCode))
            ->selectRaw('status_id, COUNT(*) as total')
            ->groupBy('status_id')
            ->pluck('total', 'status_id');

        $data = [];
        foreach ($idToCode as $id => $code) {
            $data[$code] = (int) ($totals[$id] ?? 0);
        }

        return response()->json([
            "status" => "success",
            "message" => "Service requests counts retrieved successfully",
            "data" => $data
        ], 200);
    }

    public function destroy(ServiceRequests $request)
    {
        $request->delete();

        return response()->json([
            "status" => "success",
            "message" => "demande de service supprimée avec succès",
        ], 200);
    }

    // ─── Helpers privés ───────────────────────────────────────────────

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

            $status = Status::whereHas('statusType', fn($q) => $q->where('code', $typeCode))
                ->where('code', $code)
                ->first();

            if (!$status) {
                throw new \Exception("Statut '$code' introuvable pour le type '$typeCode'");
            }

            return $status->id;
        });
    }

    // ✅ Ajoute un log explicite si aucun admin n'existe
    private function sendMailToAdmin()
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if (!$admin) {
            Log::warning("Aucun admin trouvé pour l'envoi de la notification de demande de service.");
            return null;
        }

        return $admin->email;
    }
    private function generateRequestNumber()
    {
        $number = ServiceRequests::count();
        $orderNumber = 'PS-' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
        return $orderNumber;
    }
}

