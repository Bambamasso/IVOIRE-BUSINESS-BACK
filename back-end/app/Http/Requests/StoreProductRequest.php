<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreProductRequest extends FormRequest
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
            //
            'created_by' => 'exists:users,id',
            'status_id' => 'exists:statuses,id',
            'category_id' => 'required|exists:categories,id',
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,jpeg,png|max:2048',
            "variantes" => 'nullable|array',
            "variantes.*.stock_quantity" => 'nullable',
            "variantes.*.price" => 'nullable',
            'variantes.*.attribute_values' => 'nullable|array',
            'variantes.*.attribute_values_id.*' => 'exists:attribute_values,id',
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
}
