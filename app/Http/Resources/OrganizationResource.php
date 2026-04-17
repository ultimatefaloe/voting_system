<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class OrganizationResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'member_count' => $this->members()->count(),
            'election_count' => $this->elections()->count(),
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,

            // Include relationships when requested
            'members' => MemberResource::collection($this->whenLoaded('members')),
            'elections' => ElectionResource::collection($this->whenLoaded('elections')),
        ];
    }
}
