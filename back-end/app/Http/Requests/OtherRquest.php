<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;
class OtherRquest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [

            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'email' => 'required|email',
            "order_number" => "nullable|string",
            "status_id" => "nullable|exists:statuses,id",
            "user_id" => "nullable|exists:users,id",
            "phone_number" => "required|string",
            "city_id" => "required|exists:cities,id",
            "municipality_id" => "required|exists:municipalities,id",
            "address" => "required|string",
            "payment_method" => "required|string|in:online,cash_on_delivery",
            "total_amount" => "nullable|numeric|min:0"

        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()

            ], 422)
        );
    }


    public function messages(): array
    {
        return [
            "phone_number.string" => "Le numéro de téléphone doit être une chaîne de caractères.",
            "city_id.exists" => "La ville spécifiée n'existe pas.",
            "municipality_id.exists" => "La municipalité spécifiée n'existe pas.",
            "address.string" => "L'adresse doit être une chaîne de caractères.",
            "payment_method.string" => "La méthode de paiement doit être une chaîne de caractères."
        ];
    }
}
