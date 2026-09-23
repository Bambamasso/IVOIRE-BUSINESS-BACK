<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class ContactController extends Controller
{
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'full_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'phone_number' => 'required|string|max:30',
            'subject' => 'required|string|max:255',
            'message' => 'required|string|max:5000',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validation error',
                'errors' => $validator->errors()->first(),
            ], 422);
        }

        $adminEmail = $this->adminEmail();

        if (!$adminEmail) {
            Log::warning("Aucun admin trouvé pour l'envoi du message de contact.");
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.',
            ], 500);
        }

        try {
            Mail::to($adminEmail)->send(new ContactMail($validator->validated()));
        } catch (\Exception $e) {
            Log::error("Erreur lors de l'envoi du mail de contact : " . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.',
            ], 500);
        }

        return response()->json([
            'success' => true,
            'message' => 'Votre message a bien été envoyé.',
        ], 200);
    }

    private function adminEmail()
    {
        $admin = User::whereHas('roles', function ($query) {
            $query->where('name', 'admin');
        })->first();

        return $admin?->email;
    }
}

