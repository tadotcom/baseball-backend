<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class PasswordResetRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Anyone can request a password reset
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            // Only need the email to send the reset link
            'email' => ['required', 'string', 'email', 'max:255', 'exists:users,email,deleted_at,NULL'], // Check if email exists (and not soft-deleted)
        ];
    }

     /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        // Avoid revealing if email exists or not in error messages for security
        // Use generic messages or map only format errors
        return [
            'email.required' => 'メールアドレスは必須です',
            'email.email' => 'E-422-04: メールアドレスの形式が正しくありません', //
            'email.exists' => '指定されたメールアドレスは見つかりませんでした。', // Generic message
        ];
    }
}