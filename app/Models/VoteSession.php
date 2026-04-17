<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['election_id', 'voter_token', 'ip_address', 'user_agent', 'submitted_at'])]
class VoteSession extends Model
{
    use HasFactory;

    /**
     * Get the election
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get all votes in this session
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get count of votes in this session
     */
    public function getVoteCount(): int
    {
        return $this->votes()->count();
    }

    /**
     * Get votes grouped by position
     */
    public function getVotesByPosition(): array
    {
        return $this->votes()
            ->with('position')
            ->get()
            ->groupBy('position_id')
            ->map(function ($votes, $positionId) {
                return [
                    'position_id' => $positionId,
                    'vote_count' => $votes->count(),
                    'candidate_ids' => $votes->pluck('candidate_id')->values()->toArray(),
                ];
            })
            ->values()
            ->toArray();
    }
}
