<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $userId = $this->route('user')?->id ?? $this->route('user');

        return [
            'first_name' => 'nullable|string|max:255',
            'last_name' => 'nullable|string|max:255',
            'username' => ['nullable', 'string', 'max:255', Rule::unique('users', 'username')->ignore($userId)],
            'civility' => 'nullable|string|max:255',
            'email' => ['nullable', 'email', Rule::unique('users', 'email')->ignore($userId)],
            'number' => 'nullable|string|max:255',
            'password' => 'nullable|string|min:8',
            'role' => 'nullable|string|exists:roles,name',
        ];
    }

    public function messages(): array
    {
        return [
            'username.unique' => "Ce nom d'utilisateur est déjà pris.",
            'email.unique' => 'Cet email est déjà utilisé.',
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
