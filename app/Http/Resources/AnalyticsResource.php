<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AnalyticsResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'organization_id' => $this->get('organization_id'),
            'organization_name' => $this->get('organization_name'),

            // Overall statistics
            'overall_statistics' => [
                'total_elections' => $this->get('overall_statistics.total_elections', 0),
                'total_voters' => $this->get('overall_statistics.total_voters', 0),
                'total_votes' => $this->get('overall_statistics.total_votes', 0),
                'average_turnout_percentage' => $this->get('overall_statistics.average_turnout_percentage', 0),
                'total_positions' => $this->get('overall_statistics.total_positions', 0),
                'total_candidates' => $this->get('overall_statistics.total_candidates', 0),
            ],

            // Elections data
            'elections' => $this->get('elections', []),

            // Trends
            'trends' => $this->get('trends', []),

            // Metrics
            'metrics' => $this->get('metrics', []),
        ];
    }
}
