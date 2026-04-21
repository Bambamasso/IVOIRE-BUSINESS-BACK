<?php

namespace App\Http\Requests;

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
            "validated_by" => "nullable|exists:users,id",
            "rejected_by" => "nullable|exists:users,id",
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
