<?php

namespace App\Http\Requests\Game; // Correct namespace

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreGameRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Authorization handled by AdminMiddleware for POST /games
        return true;
    }

    public function rules(): array
    {
        // Prefectures based on fixed rule
        $prefectures = ['北海道', '青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県', '茨城県', '栃木県', '群馬県', '埼玉県', '千葉県', '東京都', '神奈川県', '新潟県', '富山県', '石川県', '福井県', '山梨県', '長野県', '岐阜県', '静岡県', '愛知県', '三重県', '滋賀県', '京都府', '大阪府', '兵庫県', '奈良県', '和歌山県', '鳥取県', '島根県', '岡山県', '広島県', '山口県', '徳島県', '香川県', '愛媛県', '高知県', '福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県', '沖縄県'];

        return [
            'place_name' => ['required', 'string', 'max:254'], //
            'game_date_time' => ['required', 'date', 'after:now +1 hour'], //
            'address' => ['required', 'string', 'max:254'], //
            'prefecture' => ['required', 'string', Rule::in($prefectures)], //
            'latitude' => ['required', 'numeric', 'between:-90,90'], //
            'longitude' => ['required', 'numeric', 'between:-180,180'], //
            'acceptable_radius' => ['required', 'integer', 'min:1', 'max:1999'], //
            'fee' => ['nullable', 'integer', 'min:0'], //
            'capacity' => ['required', 'integer', 'min:18'], //
        ];
    }

    public function messages(): array
    {
        // Map error codes
        return [
            'game_date_time.after' => 'E-422-01: 開催日時は現在時刻の1時間後以降を指定してください',
            'prefecture.in' => 'E-422-02: 都道府県が正しくありません',
            'capacity.min' => 'E-422-03: 募集人数は18人以上を指定してください',
            // Add other messages corresponding to error codes and rules (required, string, numeric etc.)
            'place_name.required' => 'E-422-XX: 場所名は必須です',
            'address.required' => 'E-422-XX: 住所は必須です',
            // ...
        ];
    }
}