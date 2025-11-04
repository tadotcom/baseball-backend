<?php

namespace App/Http/Requests/Admin; // Namespace for Admin requests

use Illuminate/Foundation/Http/FormRequest;
use Illuminate/Validation/Rule;

class StoreEmailNotificationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Route middleware 'auth:sanctum' and 'admin' handle authorization
        return true;
    }

    /**
     * Get the validation rules that apply to the request. (F-ADM-010)
     *
     * @return array<string, \Illuminate/Contracts/Validation/ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Similar target structure as push notifications
        $targetTypes = ['all', 'game', 'users'];

        return [
            'subject' => ['required', 'string', 'max:100'], // Email subject
            'body' => ['required', 'string'], // Email body (HTML format expected)

            // --- Target Definition ---
            'target' => ['required', 'array'],
            'target.type' => ['required', 'string', Rule::in($targetTypes)],
            'target.game_id' => ['required_if:target.type,game', 'nullable', 'string', 'uuid', 'exists:games,game_id'],
            'target.user_ids' => ['required_if:target.type,users', 'nullable', 'array'],
            'target.user_ids.*' => ['required_if:target.type,users', 'string', 'uuid', 'exists:users,user_id,deleted_at,NULL'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     */
    public function messages(): array
    {
        // Similar messages as StorePushNotificationRequest, adjusted for email context
        return [
            'subject.required' => 'E-422-XX: メール件名は必須です',
            'body.required' => 'E-422-XX: メール本文は必須です',
            'target.required' => 'E-422-XX: 配信対象の指定は必須です',
            'target.type.in' => 'E-422-XX: 配信対象のタイプが不正です',
            'target.game_id.required_if' => 'E-422-XX: 配信対象タイプが \'game\' の場合、試合IDは必須です',
            'target.game_id.exists' => 'E-404-02: 指定された試合が見つかりません',
            'target.user_ids.required_if' => 'E-422-XX: 配信対象タイプが \'users\' の場合、ユーザーIDリストは必須です',
            'target.user_ids.array' => 'E-422-XX: ユーザーIDリストは配列で指定してください',
            'target.user_ids.*.uuid' => 'E-400-02: ユーザーIDの形式が正しくありません',
            'target.user_ids.*.exists' => 'E-404-01: 指定されたユーザーが見つからないか、退会済みです',
        ];
    }
}