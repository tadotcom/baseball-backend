<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;

class LoginRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone can attempt to login
    }

    public function rules(): array
    {
        return [
            'email' => ['required', 'string', 'email', 'max:255'], //
            'password' => ['required', 'string', 'min:8', 'max:72'], //
        ];
    }

     public function messages(): array
    {
        return [
            'email.required' => 'E-422-XX: メールアドレスは必須です',
            'email.email' => 'E-422-04: メールアドレスの形式が正しくありません',
            'password.required' => 'E-422-XX: パスワードは必須です',
            'password.min' => 'E-422-05: パスワードは8文字以上で入力してください', // Technically not needed on login, but consistent
        ];
    }
}