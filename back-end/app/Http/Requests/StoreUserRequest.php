<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'username' => 'nullable|string|max:255|unique:users,username',
            'civility' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'number' => 'required|string|max:255',
            'password' => 'nullable|string|min:8|confirmed',
            'role' => 'required|string|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.required' => 'Le prénom est obligatoire.',
            'last_name.required' => 'Le nom est obligatoire.',
            'username.unique' => "Ce nom d'utilisateur est déjà pris.",
            'civility.required' => 'La civilité est obligatoire.',
            'email.required' => "L'email est obligatoire.",
            'email.unique' => 'Cet email est déjà utilisé.',
            'number.required' => 'Le numéro de téléphone est obligatoire.',
            'password.confirmed' => 'La confirmation du mot de passe ne correspond pas.',
            'role.required' => 'Le rôle est obligatoire.',
            'role.exists' => "Le rôle sélectionné n'existe pas.",
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
