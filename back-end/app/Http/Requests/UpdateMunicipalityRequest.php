<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateMunicipalityRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'city_id' => 'sometimes|required|exists:cities,id',
            'name' => 'required|string|max:255',
            'shipping_fee' => 'nullable|numeric|min:0',
        ];
    }

    public function messages(): array
    {
        return [
            'city_id.required' => 'La ville est obligatoire.',
            'city_id.exists' => 'La ville sélectionnée n\'existe pas.',
            'name.required' => 'Le nom de la commune est obligatoire.',
            'shipping_fee.numeric' => 'Les frais de livraison doivent être un nombre valide.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'message' => 'Validation failed',
            'errors' => $validator->errors()
        ], 422));
    }
}
