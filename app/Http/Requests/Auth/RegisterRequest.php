<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;

class RegisterRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Anyone can attempt to register
    }

    public function rules(): array
    {
        return [
            // Validation rules based on fixed rules
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,NULL,user_id,deleted_at,NULL'], // Unique check ignoring soft-deleted
            'password' => [
                'required',
                'string',
                Password::min(8) // Min 8 characters
                        // ->mixedCase() // Requires uppercase and lowercase (Adjust rule if needed)
                        ->numbers() // Requires numbers
                        // ->symbols() // Optional: Requires symbols
                        ->uncompromised(), // Check if password has been exposed in data breaches
                'max:72', // Bcrypt limit
                'confirmed' // Requires password_confirmation field
            ],
            // Rule for 2 character types - Use custom rule or regex if Password rule doesn't cover it exactly
             'password' => ['required', 'string', 'min:8', 'max:72', 'confirmed', 'regex:/^(?=.*[a-zA-Z])(?=.*[0-9]).*$/'], // Example: At least one letter and one number

            'nickname' => [
                'required',
                'string',
                'size:4', // Fixed 4 characters
                // TODO: Add validation for allowed characters (hiragana, katakana, kanji, alphanumeric) if needed
                'unique:users,nickname,NULL,user_id,deleted_at,NULL' // Unique check ignoring soft-deleted
            ],
        ];
    }

     public function messages(): array
    {
        // Map error codes
        return [
            'email.required' => 'E-422-XX: メールアドレスは必須です',
            'email.email' => 'E-422-04: メールアドレスの形式が正しくありません',
            'email.unique' => 'E-409-01: このメールアドレスは既に登録されています',
            'password.required' => 'E-422-XX: パスワードは必須です',
            'password.min' => 'E-422-05: パスワードは8文字以上72文字以下で入力してください',
            'password.max' => 'E-422-05: パスワードは8文字以上72文字以下で入力してください',
            'password.regex' => 'E-422-06: パスワードは英字と数字をそれぞれ1文字以上含めてください',
            'password.confirmed' => 'E-422-08: パスワード確認が一致しません',
            'nickname.required' => 'E-422-XX: ニックネームは必須です',
            'nickname.size' => 'E-422-07: ニックネームは4文字で入力してください',
            'nickname.unique' => 'E-409-02: このニックネームは既に使用されています',
        ];
    }
}