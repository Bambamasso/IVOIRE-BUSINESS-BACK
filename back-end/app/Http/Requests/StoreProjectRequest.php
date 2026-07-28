<?php

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreProjectRequest extends FormRequest
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
            'location' => 'required|string|max:255',
            'task' => 'required|string|max:255',
            'year' => 'required|string|max:255',
            'client' => 'required|string|max:255',
        ];
    }
    // public function messages(): array
    // {
    //     // return[
    //     //     'location.required' => 'Le champ "location" est requis.',
    //     //     'location.string' => 'Le champ "location" doit être une chaîne de caractères.',
    //     //     'location.max' => 'Le champ "location" ne doit pas dépasser 255 caractères.',
    //     //     'task.required' => 'Le champ "tâche" est requis.',
    //     //     'task.string' => 'Le champ "tâche" doit être une chaîne de caractères.',
    //     //     'task.max' => 'Le champ "task" ne doit pas dépasser 255 caractères.',
    //     //     'year.required' => 'Le champ "year" est requis.',
    //     //     'year.integer' => 'Le champ "year" doit être un entier.',
    //     //     'year.min' => 'Le champ "year" doit être au moins 1900.',
    //     //     'year.max' => 'Le champ "year" ne peut pas être supérieur à l\'année en cours.',
    //     //     'client.required' => 'Le champ "client" est requis.',
    //     //     'client.string' => 'Le champ "client" doit être une chaîne de caractères.',
    //     //     'client.max' => 'Le champ "client" ne doit pas dépasser 255 caractères.',
    //     // ];

    // }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'status' => 'error',
            'errors' => $validator->errors()
        ], 422));
    }
}
