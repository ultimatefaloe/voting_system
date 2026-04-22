<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class ElectionDetailPageController extends Controller
{
    public function __invoke(Election $election): Response
    {
        $this->authorize('view', $election);

        $election->load([
            'organization:id,name,slug',
            'positions:id,election_id,title,description,max_votes,order',
            'positions.candidates:id,position_id,name,bio,avatar,order',
        ]);

        $election->loadCount([
            'positions',
            'voteSessions',
        ]);

        $positions = $election->positions
            ->map(function ($position) {
                return [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'max_votes' => $position->max_votes,
                    'candidates_count' => $position->candidates->count(),
                    'candidates' => $position->candidates
                        ->sortBy('order')
                        ->map(function ($candidate) {
                            return [
                                'id' => $candidate->id,
                                'name' => $candidate->name,
                                'bio' => $candidate->bio,
                                'avatar' => $candidate->avatar,
                                'order' => $candidate->order,
                            ];
                        })
                        ->values(),
                ];
            })
            ->values();

        $user = Auth::user();

        return Inertia::render('elections/show', [
            'electionData' => [
                'election' => [
                    'id' => $election->id,
                    'organization_id' => $election->organization_id,
                    'organization' => $election->organization?->name,
                    'organization_slug' => $election->organization?->slug,
                    'title' => $election->title,
                    'description' => $election->description,
                    'type' => $election->type,
                    'status' => $election->status,
                    'start_date' => $election->start_date?->toDateString(),
                    'end_date' => $election->end_date?->toDateString(),
                    'created_at' => $election->created_at?->toDateString(),
                ],
                'summary' => [
                    'positions_count' => $election->positions_count,
                    'vote_sessions_count' => $election->vote_sessions_count,
                ],
                'positions' => $positions,
                'permissions' => [
                    'can_update' => $user ? $user->can('update', $election) : false,
                    'can_start' => $user ? $user->can('start', $election) : false,
                    'can_stop' => $user ? $user->can('stop', $election) : false,
                    'can_publish' => $user ? $user->can('publish', $election) : false,
                ],
                'lifecycle' => [
                    'can_start_now' => $election->canStart(),
                    'can_stop_now' => $election->canStop(),
                    'can_publish_now' => $election->canPublish(),
                ],
            ],
        ]);
    }
}
