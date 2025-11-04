<?php

namespace App\Http\Requests\Game;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class FilterGameRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     * Authorization is checked by middleware on the route itself.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request's query parameters.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        // Prefectures based on fixed rule
         $prefectures = ['北海道', /* ... */ '沖縄県'];
         // Statuses for admin filter
         $statuses = ['募集中', '満員', '開催済み', '中止'];

        return [
            // Pagination rules
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'], // Max 100 per page

            // Filter rules
            'prefecture' => ['nullable', 'string', Rule::in($prefectures)],
            'date_from' => ['nullable', 'date_format:Y-m-d'], // Validate format
            'date_to' => ['nullable', 'date_format:Y-m-d', 'after_or_equal:date_from'], // Ensure to is after from
             // Optional status filter for admin endpoint
             'status' => ['nullable', 'string', Rule::in($statuses)],
        ];
    }

     /**
     * Get custom error messages for validator errors.
     */
     public function messages(): array
    {
         //
         return [
             'page.integer' => 'ページ番号は整数で指定してください。',
             'per_page.integer' => '表示件数は整数で指定してください。',
             'per_page.max' => '一度に取得できる件数は100件までです。',
             'prefecture.in' => 'E-422-02: 都道府県が正しくありません。',
             'date_from.date_format' => 'E-400-03: 開始日の形式はYYYY-MM-DDで指定してください。',
             'date_to.date_format' => 'E-400-03: 終了日の形式はYYYY-MM-DDで指定してください。',
             'date_to.after_or_equal' => '終了日は開始日以降の日付を指定してください。',
             'status.in' => 'ステータスが不正です。',
         ];
    }

     /**
     * Prepare the data for validation.
     * Set default values here if needed, although defaults are often handled in the Controller/Service.
     */
    // protected function prepareForValidation(): void
    // {
    //     $this->mergeIfMissing([
    //         'page' => 1,
    //         'per_page' => 20,
    //     ]);
    // }
}