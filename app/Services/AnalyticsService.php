<?php

namespace App\Services;

use App\Models\Election;
use App\Models\Organization;

class AnalyticsService
{
    public function __construct(private ResultsService $resultsService)
    {
    }

    /**
     * Get organization-wide analytics across all elections.
     *
     * @param Organization $organization
     * @return array
     */
    public function getOrganizationAnalytics(Organization $organization): array
    {
        $elections = $organization->elections()
            ->where('status', 'published')
            ->get();

        $totalElections = $elections->count();
        $totalVoters = 0;
        $totalVotes = 0;
        $totalParticipation = 0;

        $electionStats = $elections->map(function (Election $election) {
            try {
                $stats = $this->resultsService->getDetailedAnalytics($election);

                return [
                    'election_id' => $election->id,
                    'title' => $election->title,
                    'total_voters' => $stats['voters']['total_voters'],
                    'total_voted' => $stats['voters']['total_voted'],
                    'turnout_percentage' => $stats['voters']['turnout_percentage'],
                    'total_votes' => $stats['votes']['total_votes'],
                ];
            } catch (\Exception $e) {
                return null;
            }
        })->filter(fn ($s) => $s !== null)->values();

        if ($totalElections > 0) {
            $totalVoters = $electionStats->sum('total_voters');
            $totalVotes = $electionStats->sum('total_votes');
            $totalParticipation = $electionStats->sum('total_voted');
        }

        $averageTurnout = $totalElections > 0
            ? round($electionStats->avg('turnout_percentage'), 2)
            : 0;

        return [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'overall_statistics' => [
                'total_elections' => $totalElections,
                'total_voters_across_elections' => $totalVoters,
                'total_votes_cast' => $totalVotes,
                'total_participants' => $totalParticipation,
                'average_turnout_percentage' => $averageTurnout,
                'average_votes_per_election' => $totalElections > 0 ? round($totalVotes / $totalElections, 2) : 0,
            ],
            'elections' => $electionStats,
        ];
    }

    /**
     * Get election trends over time.
     *
     * @param Organization $organization
     * @return array
     */
    public function getElectionTrends(Organization $organization): array
    {
        $elections = $organization->elections()
            ->where('status', 'published')
            ->orderBy('created_at', 'asc')
            ->get();

        $trends = [];
        $cumulativeVotes = 0;
        $cumulativeVoters = 0;

        foreach ($elections as $election) {
            try {
                $stats = $this->resultsService->getDetailedAnalytics($election);

                $cumulativeVotes += $stats['votes']['total_votes'];
                $cumulativeVoters += $stats['voters']['total_voted'];

                $trends[] = [
                    'election_id' => $election->id,
                    'title' => $election->title,
                    'date' => $election->created_at->toIso8601String(),
                    'turnout_percentage' => $stats['voters']['turnout_percentage'],
                    'total_votes' => $stats['votes']['total_votes'],
                    'cumulative_votes' => $cumulativeVotes,
                    'cumulative_voters' => $cumulativeVoters,
                ];
            } catch (\Exception $e) {
                // Skip elections that can't be analyzed
            }
        }

        return [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'trends' => $trends,
        ];
    }

    /**
     * Get member participation analytics.
     *
     * @param Organization $organization
     * @return array
     */
    public function getMemberParticipationAnalytics(Organization $organization): array
    {
        $elections = $organization->elections()
            ->where('status', 'published')
            ->get();

        $memberParticipation = [];

        foreach ($elections as $election) {
            $voters = $election->voteSessions()
                ->select('voter_token')
                ->distinct()
                ->get();

            // Map voter tokens to members if available
            // This is a placeholder - actual implementation depends on your voter model
            $memberParticipation[$election->id] = [
                'election_id' => $election->id,
                'title' => $election->title,
                'unique_voters' => $voters->count(),
                'vote_count' => $election->votes()->count(),
            ];
        }

        return [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'member_participation' => array_values($memberParticipation),
        ];
    }

    /**
     * Get most competitive elections (closest races).
     *
     * @param Organization $organization
     * @param int $limit
     * @return array
     */
    public function getMostCompetitiveElections(Organization $organization, int $limit = 5): array
    {
        $elections = $organization->elections()
            ->where('status', 'published')
            ->get();

        $competitiveness = [];

        foreach ($elections as $election) {
            try {
                $analytics = $this->resultsService->getDetailedAnalytics($election);

                $totalCompetitiveness = 0;

                foreach ($analytics['positions'] as $position) {
                    if (count($position['candidates']) >= 2) {
                        $topVotes = $position['candidates'][0]['votes'] ?? 0;
                        $secondVotes = $position['candidates'][1]['votes'] ?? 0;
                        $margin = $topVotes - $secondVotes;

                        // Smaller margin = more competitive
                        $totalCompetitiveness += abs($margin);
                    }
                }

                $competitiveness[] = [
                    'election_id' => $election->id,
                    'title' => $election->title,
                    'competitiveness_score' => $totalCompetitiveness,
                    'total_votes' => $analytics['votes']['total_votes'],
                ];
            } catch (\Exception $e) {
                // Skip
            }
        }

        usort($competitiveness, fn ($a, $b) => $a['competitiveness_score'] <=> $b['competitiveness_score']);

        return [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'most_competitive_elections' => array_slice($competitiveness, 0, $limit),
        ];
    }

    /**
     * Get high turnout elections.
     *
     * @param Organization $organization
     * @param int $limit
     * @return array
     */
    public function getHighTurnoutElections(Organization $organization, int $limit = 5): array
    {
        $elections = $organization->elections()
            ->where('status', 'published')
            ->get();

        $turnoutStats = [];

        foreach ($elections as $election) {
            try {
                $analytics = $this->resultsService->getDetailedAnalytics($election);

                $turnoutStats[] = [
                    'election_id' => $election->id,
                    'title' => $election->title,
                    'turnout_percentage' => $analytics['voters']['turnout_percentage'],
                    'total_voted' => $analytics['voters']['total_voted'],
                    'total_voters' => $analytics['voters']['total_voters'],
                ];
            } catch (\Exception $e) {
                // Skip
            }
        }

        usort($turnoutStats, fn ($a, $b) => $b['turnout_percentage'] <=> $a['turnout_percentage']);

        return [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'high_turnout_elections' => array_slice($turnoutStats, 0, $limit),
        ];
    }

    /**
     * Get candidate performance analytics (across elections).
     *
     * @param Organization $organization
     * @return array
     */
    public function getCandidatePerformance(Organization $organization): array
    {
        $elections = $organization->elections()
            ->where('status', 'published')
            ->with(['positions.candidates.votes'])
            ->get();

        $candidatePerformance = [];

        foreach ($elections as $election) {
            foreach ($election->positions as $position) {
                foreach ($position->candidates as $candidate) {
                    $candidateKey = $candidate->id;

                    if (!isset($candidatePerformance[$candidateKey])) {
                        $candidatePerformance[$candidateKey] = [
                            'candidate_id' => $candidate->id,
                            'name' => $candidate->name,
                            'total_votes' => 0,
                            'elections_participated' => 0,
                            'winning_positions' => 0,
                            'losing_positions' => 0,
                        ];
                    }

                    $voteCount = $candidate->votes()->count();
                    $candidatePerformance[$candidateKey]['total_votes'] += $voteCount;
                    $candidatePerformance[$candidateKey]['elections_participated'] += 1;

                    // Check if candidate won this position
                    $topCandidate = $position->candidates()
                        ->withCount('votes')
                        ->orderByDesc('votes_count')
                        ->first();

                    if ($topCandidate && $topCandidate->id === $candidate->id) {
                        $candidatePerformance[$candidateKey]['winning_positions'] += 1;
                    } else {
                        $candidatePerformance[$candidateKey]['losing_positions'] += 1;
                    }
                }
            }
        }

        usort(
            $candidatePerformance,
            fn ($a, $b) => $b['total_votes'] <=> $a['total_votes'],
        );

        return [
            'organization_id' => $organization->id,
            'organization_name' => $organization->name,
            'candidate_performance' => array_values($candidatePerformance),
        ];
    }
}
