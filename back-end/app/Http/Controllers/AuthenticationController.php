<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class AuthenticationController extends Controller
{
    //
    public function redirect(): RedirectResponse
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback(): JsonResponse
    {
        try {
            $socialiteUser = Socialite::driver('google')->stateless()->user();

            $user = User::updateOrCreate([
                'email' => $socialiteUser->getEmail(),
                'google_id' => $socialiteUser->getId(),
            ], [
                'name' => $socialiteUser->getName(),
            ]);

            Auth::login($user);
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json(['token' => $token]);
        } catch (\Exception $e) {
         return response()->json(['error' => $e->getMessage()], 500);

        }

    }
}
