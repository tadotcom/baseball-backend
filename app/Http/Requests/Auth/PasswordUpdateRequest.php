<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class PasswordUpdateRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Token check is handled by PasswordBroker
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'token' => ['required', 'string'], // The password reset token from the email link
            'email' => ['required', 'string', 'email', 'max:255'], // Email associated with the token
            'password' => [ // New password validation
                'required',
                'string',
                 Password::min(8)->numbers(), // Enforce rules for new password
                'max:72',
                'confirmed', // Requires password_confirmation field
                // Add regex for 2 character types if needed
                'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).*$/',
            ],
        ];
    }

     /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        //
        return [
            'token.required' => 'E-400-06: パスワードリセットトークンが必要です', // Or generic
            'email.required' => 'メールアドレスは必須です',
            'email.email' => 'E-422-04: メールアドレスの形式が正しくありません',
            'password.required' => '新しいパスワードは必須です',
            'password.min' => 'E-422-05: パスワードは8文字以上72文字以下で入力してください',
            'password.max' => 'E-422-05: パスワードは8文字以上72文字以下で入力してください',
            'password.regex' => 'E-422-06: パスワードは英字と数字をそれぞれ1文字以上含めてください',
            'password.confirmed' => 'E-422-08: パスワード確認が一致しません',
        ];
    }
}