<?php

namespace App\Http\Controllers;

use App\Models\OrganizationMember;
use App\Models\VoteSession;
use Inertia\Inertia;
use Inertia\Response;

class VotingSessionsPageController extends Controller
{
    public function __invoke(): Response
    {
        $user = request()->user();

        $organizationIds = OrganizationMember::query()
            ->where('user_id', $user->id)
            ->where('status', 'active')
            ->pluck('organization_id');

        $sessionsBase = VoteSession::query()
            ->whereHas('election', fn ($query) => $query->whereIn('organization_id', $organizationIds));

        $sessions = (clone $sessionsBase)
            ->with([
                'election:id,organization_id,title,status,type',
                'election.organization:id,name',
            ])
            ->withCount('votes')
            ->orderByDesc('submitted_at')
            ->limit(50)
            ->get()
            ->map(function (VoteSession $session) {
                return [
                    'id' => $session->id,
                    'election_id' => $session->election_id,
                    'election_title' => $session->election?->title,
                    'election_status' => $session->election?->status,
                    'election_type' => $session->election?->type,
                    'organization' => $session->election?->organization?->name,
                    'votes_count' => $session->votes_count,
                    'submitted_at' => $session->submitted_at?->toDateTimeString(),
                    'created_at' => $session->created_at?->toDateTimeString(),
                ];
            })
            ->values();

        return Inertia::render('voting-sessions/index', [
            'votingSessionsData' => [
                'items' => $sessions,
                'summary' => [
                    'total_sessions' => (clone $sessionsBase)->count(),
                    'total_votes_cast' => (clone $sessionsBase)->withCount('votes')->get()->sum('votes_count'),
                    'elections_with_activity' => (clone $sessionsBase)->distinct('election_id')->count('election_id'),
                ],
            ],
        ]);
    }
}
