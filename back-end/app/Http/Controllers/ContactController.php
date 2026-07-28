<?php

namespace App\Http\Controllers;

use App\Mail\ContactMail;
use App\Models\User;
use Illuminate\Http\Request;
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

        try {
            Mail::to($this->adminEmail())
                ->send(new ContactMail($validator->validated()));
        } catch (\Exception $e) {
            throw new \Exception('Une erreur est survenue lors de l\'envoi du message. Veuillez réessayer plus tard.' . $e->getMessage());
        }


        return response()->json([
            'success' => true,
            'message' => 'Votre message a bien été envoyé.',
        ], 200);
    }

    private function adminEmail()
    {
        $adminEmail = User::role('admin')->first();

        return $adminEmail->email;
    }
}

