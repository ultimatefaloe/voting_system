<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['position_id', 'name', 'bio', 'avatar', 'order'])]
class Candidate extends Model
{
    use HasFactory;

    /**
     * Get the position
     */
    public function position(): BelongsTo
    {
        return $this->belongsTo(Position::class);
    }

    /**
     * Get votes for this candidate
     */
    public function votes(): HasMany
    {
        return $this->hasMany(Vote::class);
    }

    /**
     * Get total votes for this candidate
     */
    public function getVoteCount(): int
    {
        return $this->votes()->count();
    }
}
