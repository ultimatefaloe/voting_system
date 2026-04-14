<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class VoteResource extends JsonResource
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
            'candidate_id' => $this->candidate_id,
            'voter_token' => $this->voter_token,
            'vote_session_id' => $this->vote_session_id,
            'created_at' => $this->created_at,

            // Include relationships when loaded
            'candidate' => new CandidateResource($this->whenLoaded('candidate')),
        ];
    }
}
