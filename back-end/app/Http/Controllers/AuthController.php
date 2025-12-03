<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function register(Request $request)
    {
        $rules = [
            'email' => 'required|unique:users,email|email|max:255',
            'name' => 'required',
            'password' => 'required|',
            'number' => 'required'
        ];
        $customMessage = [
            'email.required' => "Veuillez saisir votre adresse email.",
            'email.email' => "Veuillez saisir une adresse email valide.",
            'email.unique' => "Cet email est déjà utilisé. Veuillez en choisir un autre ou vous connecter.",
            'name.required' => "Veuillez renseigner votre nom.",
            'number.required' => "Veuillez indiquer votre numéro.",
            'password.required' => "Veuillez définir un mot de passe.",
            // 'password.confirmed' => "Les deux mots de passe saisis ne sont pas identiques.",
        ];

        $validator = Validator::make($request->all(), $rules, $customMessage);

        if ($validator->fails()) {
            return [
                'status' => 404,
                "message" => $validator->errors()->first()
            ];
        }
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'number' => $request->number,
            'password' => Hash::make($request->password)
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'sucesss',
            'access_token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Incription réussie.',
            'user' => $user,
        ], 200);

        //
    }

    /**
     * Display the specified resource.
     */
    public function login(Request $request)
    {
        $rules = [
            'email' => 'required|string|email|max:255',
            'password' => 'required|string',

        ];
        $customMessage = [
            'email.required' => "Veuillez saisir votre adresse email.",
            'password.required' => "Veuillez saisir votre mot de passe.",
        ];
        $validator = Validator::make($request->all(), $rules, $customMessage);
        if ($validator->fails()) {
            return [
                'status' => 404,
                "message" => $validator->errors()->first()
            ];
        }
        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Information de connexion invalide'], 401);
        }
        $token = $user->createToken('auth_token')->plainTextToken;
        return response()->json([
            'status' => 'sucesss',
            'token' => $token,
            'token_type' => 'Bearer',
            'message' => 'Connexion réussie.',
            'user' => $user,

        ], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => 'Déconnexion réussie.'
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function update(string $id)
    {
        //
    }
}
