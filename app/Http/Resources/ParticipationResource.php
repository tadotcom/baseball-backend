<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ParticipationResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Defines the JSON structure for a Participation record.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this refers to the Participation model instance
        return [
            'participation_id' => $this->participation_id, //
            'game_id' => $this->game_id, //
            'user_id' => $this->user_id, //
            'team_division' => $this->team_division, //
            'position' => $this->position, //
            'status' => $this->status, // '参加確定', 'チェックイン済'

            // Conditionally include the related User details when loaded
            'user' => new UserResource($this->whenLoaded('user')),

            // Timestamps (optional)
            // 'created_at' => $this->created_at->toIso8601String(),
            // 'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}