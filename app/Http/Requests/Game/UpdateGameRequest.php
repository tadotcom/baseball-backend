<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // Authorization handled by AdminMiddleware
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     * Often similar to Store, but fields might be optional ('sometimes')
     * or have different constraints (e.g., game_date_time update rules).
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Prefectures based on fixed rule
        $prefectures = ['北海道', /* ... */ '沖縄県'];
        // Game statuses based on fixed rule
        $statuses = ['募集中', '満員', '開催済み', '中止'];

        return [
            // Use 'sometimes' if you allow partial updates (PATCH)
            // If using PUT, all fields might be required depending on API design
            'place_name' => ['sometimes','required', 'string', 'max:254'],
            // Rule for updating date might be different, e.g., cannot update if game is too close
            'game_date_time' => ['sometimes','required', 'date', 'after:now +1 hour'], // Keep same rule for simplicity
             // Status update allowed for Admin
             'status' => ['sometimes', 'required', 'string', Rule::in($statuses)],
            'address' => ['sometimes','required', 'string', 'max:254'],
            'prefecture' => ['sometimes','required', 'string', Rule::in($prefectures)],
            'latitude' => ['sometimes','required', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes','required', 'numeric', 'between:-180,180'],
            'acceptable_radius' => ['sometimes','required', 'integer', 'min:1', 'max:1999'],
            'fee' => ['nullable', 'integer', 'min:0'], // Nullable fields don't need 'sometimes' if always present or nullable
            'capacity' => ['sometimes','required', 'integer', 'min:18'],
        ];
    }

    /**
     * Get custom error messages for validator errors.
     *
     */
    public function messages(): array
    {
        // Similar to StoreGameRequest messages
        return [
            'game_date_time.after' => 'E-422-01: 開催日時は現在時刻の1時間後以降を指定してください',
            'status.in' => 'ステータスが不正です',
            'prefecture.in' => 'E-422-02: 都道府県が正しくありません',
            'capacity.min' => 'E-422-03: 募集人数は18人以上を指定してください',
            // ... other messages
        ];
    }
}