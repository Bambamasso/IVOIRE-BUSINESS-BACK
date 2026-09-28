<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class Recaptcha implements ValidationRule
{
    public function validate(string $attribute, mixed $value, \Closure $fail): void
    {
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $value,
        ]);

        $result = $response->json();

        if (!($result['success'] ?? false)) {
            Log::warning('reCAPTCHA échec', $result);
            $fail('La vérification anti-robot a échoué. Merci de réessayer.');
            return;
        }

        // Pour reCAPTCHA v3 : score entre 0.0 (bot) et 1.0 (humain)
        if (($result['score'] ?? 0) < 0.5) {
            Log::warning('reCAPTCHA score trop bas', $result);
            $fail('La vérification anti-robot a échoué. Merci de réessayer.');
        }
    }
}