<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

#[Fillable(['organization_id', 'title', 'description', 'type', 'status', 'start_date', 'end_date', 'access_token', 'created_by'])]
class Election extends Model
{
    use HasFactory;

    /**
     * Get the organization
     */
    public function organization(): BelongsTo
    {
        return $this->belongsTo(Organization::class);
    }

    /**
     * Get the user who created this election
     */
    public function createdBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get positions for this election
     */
    public function positions(): HasMany
    {
        return $this->hasMany(Position::class);
    }

    /**
     * Get election access tokens (for private elections)
     */
    public function access(): HasMany
    {
        return $this->hasMany(ElectionAccess::class);
    }

    /**
     * Alias for access() relationship
     */
    public function accessTokens(): HasMany
    {
        return $this->access();
    }

    /**
     * Get vote sessions for this election
     */
    public function voteSessions(): HasMany
    {
        return $this->hasMany(VoteSession::class);
    }

    /**
     * Get all votes for this election (through positions)
     */
    public function votes(): HasManyThrough
    {
        // This relationship retrieves votes through positions
        return $this->hasManyThrough(Vote::class, Position::class);
    }

    /**
     * Check if election is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active' &&
            now()->isBetween($this->start_date, $this->end_date);
    }

    /**
     * Check if election is draft
     */
    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }

    /**
     * Check if election can be started
     */
    public function canStart(): bool
    {
        return $this->status === 'draft' && $this->positions()->exists();
    }

    /**
     * Check if election can be stopped
     */
    public function canStop(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Check if election can be published
     */
    public function canPublish(): bool
    {
        return in_array($this->status, ['stopped', 'closed']);
    }

    /**
     * Generate unique access token for private elections
     */
    public static function generateAccessToken(): string
    {
        do {
            $token = 'elec_' . bin2hex(random_bytes(16));
        } while (self::where('access_token', $token)->exists());

        return $token;
    }
}
