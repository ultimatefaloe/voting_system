<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CandidateResource extends JsonResource
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
            'position_id' => $this->position_id,
            'name' => $this->name,
            'bio' => $this->bio,
            'platform' => $this->platform,
            'photo_url' => $this->photo_url,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Metrics
            'vote_count' => $this->votes()->count(),

            // Include relationships when loaded
            'position' => new PositionResource($this->whenLoaded('position')),
            'votes' => VoteResource::collection($this->whenLoaded('votes')),
        ];
    }
}
