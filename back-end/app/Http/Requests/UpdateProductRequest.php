<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class UpdateProductRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255',
            'category_id' => 'sometimes|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            'status_id' => 'sometimes|exists:statuses,id',
            // Variantes
            'variantes' => 'nullable|array',
            'variantes.*.id' => 'nullable|exists:product_variants,id',
            'variantes.*.stock_quantity' => 'nullable|integer|min:0',
            'variantes.*.price' => 'nullable|numeric|min:0',
            'variantes.*.sku' => 'nullable|string|max:100',
            'variantes.*.attribute_values' => 'nullable|array',
            'variantes.*.attribute_values.*' => 'exists:attribute_values,id',
            
        ];
    }
    public function messages(): array
    {
        return [
            'title.string' => 'Le nom du produit doit être une chaîne de caractères.',
            'title.max' => 'Le nom du produit ne peut pas dépasser 255 caractères.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',
            'description.string' => 'La description doit être un texte valide.',
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être au moins de 0 FCFA.',
            'stock_quantity.integer' => 'La quantité en stock doit être un nombre entier.',
            'stock_quantity.min' => 'La quantité en stock ne peut pas être inférieure à 0.',
            'status_id.exists' => 'Le statut spécifié n\'existe pas.',

            // Variantes
            'variantes.array' => 'Le format des variantes est invalide.',
            'variantes.*.id.exists' => 'Une des variantes spécifiée est invalide.',
            'variantes.*.stock_quantity.integer' => 'La quantité en stock de chaque variante doit être un entier.',
            'variantes.*.stock_quantity.min' => 'Le stock d\'une variante ne peut pas être inférieur à 0.',
            'variantes.*.price.numeric' => 'Le prix de chaque variante doit être un nombre.',
            'variantes.*.price.min' => 'Le prix d\'une variante doit être au moins de 0 FCFA.',
            'variantes.*.sku.string' => 'Le SKU doit être une chaîne de caractères.',
            'variantes.*.attribute_values.array' => 'Les valeurs d\'attributs doivent être un tableau.',
            'variantes.*.attribute_values.*.exists' => 'Une des valeurs d\'attribut sélectionnées est invalide.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->first(),
            ], 422)
        );

    }
}
