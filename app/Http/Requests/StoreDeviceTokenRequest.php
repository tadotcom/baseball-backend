<?php

namespace App/Http/Requests; // Namespace should match your structure

use Illuminate/Foundation/Http/FormRequest;
use Illuminate/Validation/Rule;

class StoreDeviceTokenRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Route middleware 'auth:sanctum' handles authentication check.
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate/Contracts/Validation/ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Device types based on fixed rule
        $deviceTypes = ['ios', 'android'];

        return [
            'token' => ['required', 'string', 'max:255'], // FCM token
            'device_type' => ['required', 'string', Rule::in($deviceTypes)],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'token.required' => 'E-422-XX: デバイストークンは必須です',
            'token.max' => 'E-422-XX: デバイストークンが長すぎます',
            'device_type.required' => 'E-422-XX: デバイスタイプは必須です',
            'device_type.in' => 'E-422-XX: デバイスタイプは \'ios\' または \'android\' である必要があります',
        ];
    }
}