<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Inertia\Inertia;
use Inertia\Response;

class ResultDetailPageController extends Controller
{
    public function __invoke(Election $election): Response
    {
        $this->authorize('viewResults', $election);

        $election->load([
            'organization:id,name,slug',
            'positions:id,election_id,title,description,max_votes,order',
            'positions.candidates' => function ($query) {
                $query
                    ->select(['id', 'position_id', 'name', 'bio', 'order'])
                    ->withCount('votes');
            },
        ]);

        $election->loadCount([
            'positions',
            'voteSessions',
            'votes',
        ]);

        $positions = $election->positions
            ->sortBy('order')
            ->values()
            ->map(function ($position) {
                $positionVotes = (int) $position->candidates->sum(function ($candidate) {
                    return (int) ($candidate->votes_count ?? 0);
                });

                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'max_votes' => $position->max_votes,
                    'total_votes' => $positionVotes,
                    'candidates' => $position->candidates
                        ->sortBy('order')
                        ->values()
                        ->map(function ($candidate) use ($positionVotes) {
                            $votes = (int) ($candidate->votes_count ?? 0);

                            return [
                                'id' => $candidate->id,
                                'name' => $candidate->name,
                                'bio' => $candidate->bio,
                                'votes_count' => $votes,
                                'percentage' => $positionVotes > 0
                                    ? round(($votes / $positionVotes) * 100, 2)
                                    : 0,
                            ];
                        }),
                ];
            });

        return Inertia::render('results/show', [
            'resultData' => [
                'election' => [
                    'id' => $election->id,
                    'organization' => $election->organization?->name,
                    'organization_slug' => $election->organization?->slug,
                    'title' => $election->title,
                    'status' => $election->status,
                    'type' => $election->type,
                    'start_date' => $election->start_date?->toDateString(),
                    'end_date' => $election->end_date?->toDateString(),
                ],
                'summary' => [
                    'positions_count' => (int) $election->positions_count,
                    'vote_sessions_count' => (int) $election->vote_sessions_count,
                    'votes_count' => (int) $election->votes_count,
                ],
                'positions' => $positions,
            ],
        ]);
    }
}
