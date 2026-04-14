<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
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
            'user_id' => $this->user_id,
            'organization_id' => $this->organization_id,
            'role' => $this->role,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
            'created_at' => $this->created_at,

            // Include relationships when loaded
            'user' => new UserResource($this->whenLoaded('user')),
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
        ];
    }
}
