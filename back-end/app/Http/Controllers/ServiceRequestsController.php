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
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Str;

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
        $input = $storeServiceRequest->all();
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
                        'type'=>'service_request'
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
            "data" => $request->load('service', 'status'),
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

        $completedStatus = $this->getStatus('completed', 'service');
        $rejectedStatus = $this->getStatus('rejected', 'service');

        if ($serviceRequest->status_id === $completedStatus || $serviceRequest->status_id === $rejectedStatus) {
            return response()->json([
                "status" => "error",
                "message" => "Cette demande est déjà validée ou rejetée"
            ], 422);
        }

        $serviceRequest->status_id = $completedStatus;
        $serviceRequest->validated_by = auth()->id();
        $serviceRequest->save();

        return response()->json([
            "status" => "success",
            "message" => "Service request validated successfully",
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

        $validatedStatus = $this->getStatus('completed', 'service');
        $rejectedStatus = $this->getStatus('rejected', 'service');

        if ($serviceRequest->status_id === $validatedStatus || $serviceRequest->status_id === $rejectedStatus) {
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

    public function updateNegotiatedPrice(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::find($requestId);

        if (!$serviceRequest) {
            return response()->json([
                "status" => "error",
                "message" => "Service request not found"
            ], 404);
        }

        $validatedData = validator($request->all(), [
            "negotiated_price" => "required|numeric|min:0",
        ]);

        if ($validatedData->fails()) {
            return response()->json([
                "status" => "error",
                "message" => "Validation error",
                "errors" => $validatedData->errors()
            ], 422);
        }

        $serviceRequest->negotiated_price = $validatedData->validated()['negotiated_price'];
        $serviceRequest->save();

        return response()->json([
            "status" => "success",
            "message" => "Prix négocié mis à jour avec succès",
            "data" => $serviceRequest->load('service', 'status'),
        ], 200);
    }

    public function getPendingRequests(Request $request)
    {
        $perPage = $request->get('per_page', 3);

        $pendingRequests = ServiceRequests::whereHas('status', function ($query) {
            $query->where('code', 'pending');
        })
            ->with('service', 'status')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            "status" => "success",
            "message" => "Pending service requests retrieved successfully",
            "data" => $pendingRequests
        ], 200);
    }

    public function getCompletedRequests(Request $request)
    {
        $perPage = $request->get('per_page', 4);

        $validatedRequests = ServiceRequests::whereHas('status', function ($query) {
            $query->where('code', 'completed');
        })
            ->with('service', 'status')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            "status" => "success",
            "message" => "Validated service requests retrieved successfully",
            "data" => $validatedRequests
        ], 200);
    }

    // Backward-compatible alias for a typo used by some clients/routes.
    public function getCompleteddRequests(Request $request)
    {
        return $this->getCompletedRequests($request);
    }

    public function getRejectedRequests(Request $request)
    {
        $perPage = $request->get('per_page', 4);

        $rejectedRequests = ServiceRequests::whereHas('status', function ($query) {
            $query->where('code', 'rejected');
        })
            ->with('service', 'status')
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            "status" => "success",
            "message" => "Rejected service requests retrieved successfully",
            "data" => $rejectedRequests
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
        $status = Status::whereHas('statusType', fn($q) => $q->where('code', $typeCode))
            ->where('code', $code)
            ->first();

        if (!$status) {
            throw new \Exception("Statut '$code' introuvable pour le type '$typeCode'");
        }

        return $status->id;
    }

    private function sendMailToAdmin()
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        if ($admin) {
            return $admin->email;
        }
    }

    private function generateRequestNumber()
    {
        $number = ServiceRequests::count();
        $orderNumber = 'PS-' . str_pad($number + 1, 3, '0', STR_PAD_LEFT);
        return $orderNumber;
    }
}

