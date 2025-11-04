<?php

namespace App\Http\Requests; // Namespace should match your structure

use Illuminate/Foundation/Http/FormRequest;

class CheckinRequest extends FormRequest
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
     * Get the validation rules that apply to the request. (F-USR-008)
     *
     * @return array<string, \Illuminate/Contracts/Validation/ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Latitude and Longitude validation based on fixed rules
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     * While specific errors (E-400-08, 09, 10, E-409-04) happen in the Service layer,
     * basic input validation errors (required, numeric, between) should still have messages.
     *
     */
    public function messages(): array
    {
        return [
            'latitude.required' => 'E-422-XX: 緯度は必須です',
            'latitude.numeric' => 'E-422-XX: 緯度は数値で指定してください',
            'latitude.between' => 'E-422-XX: 緯度の値が不正です (-90 ~ 90)',
            'longitude.required' => 'E-422-XX: 経度は必須です',
            'longitude.numeric' => 'E-422-XX: 経度は数値で指定してください',
            'longitude.between' => 'E-422-XX: 経度の値が不正です (-180 ~ 180)',
        ];
    }
}