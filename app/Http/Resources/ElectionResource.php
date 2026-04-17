<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ElectionResource extends JsonResource
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
            'organization_id' => $this->organization_id,
            'title' => $this->title,
            'description' => $this->description,
            'type' => $this->type,
            'status' => $this->status,
            'start_date' => $this->start_date,
            'end_date' => $this->end_date,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Metrics
            'position_count' => $this->positions()->count(),
            'candidate_count' => $this->positions()->with('candidates')->get()
                ->flatMap(fn ($pos) => $pos->candidates)->count(),

            // Include relationships when loaded
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
            'positions' => PositionResource::collection($this->whenLoaded('positions')),
            'access_tokens' => ElectionAccessResource::collection($this->whenLoaded('accessTokens')),
        ];
    }
}
