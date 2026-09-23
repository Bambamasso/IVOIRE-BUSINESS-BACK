<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class StoreAttributeValueRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'attribute_id' => 'required|exists:attributes,id',
            'value' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attribute_values', 'value')
                    ->where('attribute_id', $this->input('attribute_id'))
                    ->whereNull('deleted_at'),
            ],
            'hex-code' => 'nullable|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'attribute_id.required' => "La caractéristique est obligatoire.",
            'attribute_id.exists' => "La caractéristique sélectionnée n'existe pas.",
            'value.required' => 'La valeur est obligatoire.',
            'value.unique' => 'Cette valeur existe déjà pour cette caractéristique.',
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
