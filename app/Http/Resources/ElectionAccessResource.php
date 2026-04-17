<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionAccessResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'election_id' => $this->election_id,
            'token' => $this->token,
            'token_type' => $this->token_type,
            'eligible_voters' => $this->eligible_voters,
            'starts_at' => $this->starts_at,
            'ends_at' => $this->ends_at,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Status
            'is_active' => !$this->hasExpired(),
            'is_expired' => $this->hasExpired(),

            // Include relationships when loaded
            'election' => new ElectionResource($this->whenLoaded('election')),
        ];
    }
}
