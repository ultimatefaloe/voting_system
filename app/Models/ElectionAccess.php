<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['election_id', 'user_id', 'email', 'token', 'status', 'expires_at', 'used_at'])]
class ElectionAccess extends Model
{
    use HasFactory;

    protected $table = 'election_access';

    const STATUS_ACTIVE = 'active';
    const STATUS_USED = 'used';

    /**
     * Get the election
     */
    public function election(): BelongsTo
    {
        return $this->belongsTo(Election::class);
    }

    /**
     * Get the user (nullable for anonymous voting)
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if access token is valid and unused
     */
    public function isValid(): bool
    {
        return $this->status === self::STATUS_ACTIVE &&
            ($this->expires_at === null || now()->isBefore($this->expires_at));
    }

    /**
     * Check if token has been used
     */
    public function isUsed(): bool
    {
        return $this->status === self::STATUS_USED;
    }

    /**
     * Check if token has expired
     */
    public function hasExpired(): bool
    {
        return $this->expires_at !== null && now()->isAfter($this->expires_at);
    }

    /**
     * Mark token as used
     */
    public function markAsUsed(): void
    {
        $this->update([
            'status' => self::STATUS_USED,
            'used_at' => now(),
        ]);
    }

    /**
     * Generate unique voter token
     */
    public static function generateToken(): string
    {
        do {
            $token = 'voter_' . bin2hex(random_bytes(12));
        } while (self::where('token', $token)->exists());

        return $token;
    }
}
