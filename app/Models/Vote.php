<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['vote_session_id', 'position_id', 'candidate_id'])]
class Vote extends Model
{
    use HasFactory;

    /**
     * Get the vote session
     */
    public function voteSession(): BelongsTo
    {
        return $this->belongsTo(VoteSession::class);
    }

    /**
     * Get the position
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get the candidate
     */
    public function candidate(): BelongsTo
    {
        return $this->belongsTo(Candidate::class);
    }
}
