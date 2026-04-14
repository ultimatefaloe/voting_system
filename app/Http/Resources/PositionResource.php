<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class PositionResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'seats' => $this->seats,
            'order' => $this->order,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Metrics
            'candidate_count' => $this->candidates()->count(),
            'vote_count' => $this->candidates()
                ->with('votes')
                ->get()
                ->flatMap(fn ($cand) => $cand->votes)
                ->count(),

            // Include relationships when loaded
            'election' => new ElectionResource($this->whenLoaded('election')),
            'candidates' => CandidateResource::collection($this->whenLoaded('candidates')),
        ];
    }
}
