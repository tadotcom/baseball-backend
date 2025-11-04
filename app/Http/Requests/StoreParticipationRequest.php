<?php

namespace App\Http\Requests; // ★ (もし Game/ フォルダ内なら App\Http\Requests\Game)

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreParticipationRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        // 認証は auth:sanctum ミドルウェアで行う
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     */
    public function rules(): array
    {
        $positions = [
            '投手', '捕手', '一塁手', '二塁手', '三塁手', '遊撃手',
            '左翼手', '中堅手', '右翼手'
        ];
        
        return [
            'team_division' => ['required', 'string', Rule::in(['チームA', 'チームB'])],
            'position' => ['required', 'string', Rule::in($positions)],
        ];
    }

    /**
     * Get custom messages for validator errors.
     *
     */
    public function messages(): array
    {
        return [
            'team_division.in' => 'E-422-09: チーム区分が正しくありません',
            'position.in' => 'E-422-10: ポジションが正しくありません',
            'team_division.required' => 'チーム区分は必須です',
            'position.required' => 'ポジションは必須です',
        ];
    }
}