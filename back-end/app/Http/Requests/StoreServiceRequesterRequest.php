<?php

namespace App\Http\Requests;

use App\Rules\Recaptcha;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Contracts\Validation\Validator;

class StoreServiceRequesterRequest extends FormRequest
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
            "request_number" => "unique:services_requests,request_number",
            "status_id" => "exists:statuses,id",
            "service_id" => "required|exists:services,id",
            "full_name" => "required|string|max:255",
            "email" => "required|email|max:255",
            "phone_number" => "required|string|max:20",
            "address" => "required|string|max:255",
            "details" => "nullable|string",
            "propose_price" => "nullable|numeric",
            "negotiated_price" => "nullable|numeric",
            "files" => "nullable|array",
            "files.*"=>"nullable|file|mimes:jpg,jpeg,png,pdf,doc,docx|max:2048",
            "validated_by" => "nullable|exists:users,id",
            "rejected_by" => "nullable|exists:users,id",
        ];

    }

    public function messages()
    {
        return [
            "service_id.required" => "Le service est obligatoire.",
            "service_id.exists" => "Le service sélectionné n'existe pas.",
            "full_name.required" => "Votre nom complet est obligatoire.",
            "full_name.string" => "Le nom complet doit être une chaîne de caractères.",
            "full_name.max" => "Le nom complet ne doit pas dépasser :max caractères.",
            "email.required" => "L'adresse email est obligatoire.",
            "email.email" => "Veuillez fournir une adresse email valide.",
            "email.max" => "L'email ne doit pas dépasser :max caractères.",
            "phone_number.required" => "Le numéro de téléphone est obligatoire.",
            "phone_number.string" => "Le numéro de téléphone doit être une chaîne de caractères.",
            "phone_number.max" => "Le numéro de téléphone ne doit pas dépasser :max caractères.",
            "address.required" => "L'adresse est obligatoire.",
            "address.string" => "L'adresse doit être une chaîne de caractères.",
            "address.max" => "L'adresse ne doit pas dépasser :max caractères.",
            "details.string" => "Les détails doivent être une chaîne de caractères.",
            "propose_price.numeric" => "Le prix proposé doit être un nombre valide.",
            "negotiated_price.numeric" => "Le prix négocié doit être un nombre valide.",
            "files.array" => "Les fichiers doivent être envoyés sous forme de tableau.",
            "files.*.file" => "Chaque pièce jointe doit être un fichier valide.",
            "files.*.mimes" => "Les fichiers doivent être de type : jpg, jpeg, png, pdf, doc ou docx.",
            "files.*.max" => "Chaque fichier ne doit pas dépasser 2 Mo.",
            "request_number.unique" => "Ce numéro de demande est déjà utilisé.",
            "status_id.exists" => "Le statut sélectionné n'existe pas.",
            "validated_by.exists" => "L'utilisateur validateur n'existe pas.",
            "rejected_by.exists" => "L'utilisateur rejeteur n'existe pas.",
            'recaptcha_token' => 'required', new Recaptcha(),
        ];
    }
    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(
            response()->json([
                'status' => 'error',
                'message' => 'Validation failed',
                'errors' => $validator->errors()->first()

            ], 422)
        );
    }
}
