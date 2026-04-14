<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class InviteResource extends JsonResource
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
            'email' => $this->email,
            'token' => $this->token,
            'role' => $this->role,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'expires_at' => $this->expires_at,

            // Status flags
            'is_pending' => $this->status === 'pending',
            'is_accepted' => $this->status === 'accepted',
            'is_expired' => $this->expires_at && $this->expires_at->isPast(),

            // Include relationships when loaded
            'organization' => new OrganizationResource($this->whenLoaded('organization')),
        ];
    }
}
