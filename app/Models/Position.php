<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['election_id', 'title', 'description', 'max_votes', 'order'])]
class Position extends Model
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
     * Get candidates for this position
     */
    public function candidates(): HasMany
    {
        return $this->hasMany(Candidate::class);
    }

    /**
     * Get votes for this position
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get total votes for a candidate in this position
     */
    public function getVoteCountForCandidate(Candidate $candidate): int
    {
        return $this->votes()
            ->where('candidate_id', $candidate->id)
            ->count();
    }
}
