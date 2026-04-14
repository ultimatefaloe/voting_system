<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ResultsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'election_id' => $this->get('election_id'),
            'title' => $this->get('title'),
            'type' => $this->get('type'),
            'status' => $this->get('status'),
            'published' => $this->get('published'),

            // Voter metrics
            'voters' => [
                'total_eligible' => $this->get('total_eligible', 0),
                'total_voted' => $this->get('total_voted', 0),
                'turnout_percentage' => $this->get('turnout_percentage', 0),
            ],

            // Position results
            'positions' => $this->get('positions', []),

            // Additional data
            'created_at' => $this->get('created_at'),
            'ended_at' => $this->get('ended_at'),
        ];
    }
}
