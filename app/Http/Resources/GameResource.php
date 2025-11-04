<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class GameResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'game_id' => $this->game_id,
            'place_name' => $this->place_name,
            'game_date_time' => $this->game_date_time?->toIso8601String(),
            'address' => $this->address,
            'prefecture' => $this->prefecture,
            'latitude' => $this->latitude,
            'longitude' => $this->longitude,
            'acceptable_radius' => $this->acceptable_radius,
            'status' => $this->status,
            'fee' => $this->fee,
            'capacity' => $this->capacity,
            'participant_count' => $this->participations_count ?? $this->participations->count(),
            
            // ★ 参加状態フィールドを追加（コントローラーで設定された属性）
            'is_participating' => $this->getAttribute('is_participating') ?? false,
            'has_checked_in' => $this->getAttribute('has_checked_in') ?? false,
            
            // 詳細表示時のみ参加者リストを含める
            'participants' => $this->when(
                $this->relationLoaded('participations') && $request->route()->getName() === 'games.show',
                ParticipationResource::collection($this->whenLoaded('participations'))
            ),
            
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}