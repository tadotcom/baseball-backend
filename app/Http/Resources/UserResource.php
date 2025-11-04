<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     * Defines the JSON structure for a User.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $this refers to the User model instance being transformed
        return [
            'user_id' => $this->user_id, //
            'email' => $this->email, //
            'nickname' => $this->nickname, //
            // Only include sensitive dates if necessary and authorized
            // 'created_at' => $this->created_at->toIso8601String(),
            // 'updated_at' => $this->updated_at->toIso8601String(),
            // 'deleted_at' => $this->whenNotNull($this->deleted_at?->toIso8601String()), // Include only for admin endpoints if needed
        ];
    }
}