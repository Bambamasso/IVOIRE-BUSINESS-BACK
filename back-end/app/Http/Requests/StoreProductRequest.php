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
            'title' => 'required|string|max:255',
            'category_id' => 'required|exists:categories,id',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
            'stock_quantity' => 'nullable|integer|min:0',
            "variantes" => 'nullable|array',
            "variantes.*.stock_quantity" => 'nullable',
            "variantes.*.price" => 'nullable',
            'variantes.*.attribute_values' => 'nullable|array',
            'variantes.*.attribute_values_id.*' => 'exists:attribute_values,id',
            'files' => 'required|array',
            'files.*' => 'file|mimes:jpg,jpeg,png|max:2048',
        ];


    }

    public function messages()
    {
        return [
            // Statut
            'status_id.exists' => 'Le statut spécifié n\'existe pas.',
            // Titre / Nom
            'title.required' => 'Le nom du produit est obligatoire.',
            'title.string' => 'Le nom du produit doit être une chaîne de caractères.',
            'title.max' => 'Le nom du produit ne peut pas dépasser 255 caractères.',

            // Catégorie
            'category_id.required' => 'La catégorie est obligatoire.',
            'category_id.exists' => 'La catégorie sélectionnée n\'existe pas.',

            // Description
            'description.string' => 'La description doit être un texte valide.',

            // Prix
            'price.numeric' => 'Le prix doit être un nombre.',
            'price.min' => 'Le prix doit être au moins de 0 FCFA.',

            // Stock principal
            'stock_quantity.integer' => 'La quantité en stock doit être un nombre entier.',
            'stock_quantity.min' => 'La quantité en stock ne peut pas être inférieure à 0.',

            // Variantes
            "variantes.array" => "Le format des variantes est invalide.",
            "variantes.*.stock_quantity.integer" => "La quantité en stock de chaque variante doit être un nombre entier.",
            "variantes.*.stock_quantity.min" => "Le stock d'une variante ne peut pas être inférieur à 0.",
            "variantes.*.price.numeric" => "Le prix de chaque variante doit être un nombre.",
            "variantes.*.price.min" => "Le prix d'une variante doit être au moins de 0 FCFA.",
            'variantes.*.attribute_values.array' => "Les valeurs d'attributs de chaque variante doivent être un tableau.",
            'variantes.*.attribute_values_id.*.exists' => "L'une des valeurs d'attribut sélectionnées est invalide.",

            // Fichiers / Images
            'files.required' => 'Veuillez ajouter une ou plusieurs images pour ce produit.',
            'files.array' => 'Le format des fichiers est invalide.',
            'files.*.file' => 'Chaque fichier doit être un fichier valide.',
            'files.*.mimes' => 'Les images doivent être au format JPG, JPEG ou PNG.',
            'files.*.max' => 'Chaque image ne doit pas dépasser 2 Mo (2048 Ko).',
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
