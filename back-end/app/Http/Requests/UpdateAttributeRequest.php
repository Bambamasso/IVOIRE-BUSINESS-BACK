<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateAttributeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $attributeId = $this->route('attribute')?->id ?? $this->route('attribute');

        return [
            'name' => [
                'required',
                'string',
                'max:255',
                Rule::unique('attributes', 'name')->whereNull('deleted_at')->ignore($attributeId),
            ],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => "Le nom de la caractéristique est obligatoire.",
            'name.unique' => 'Cette caractéristique existe déjà.',
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
