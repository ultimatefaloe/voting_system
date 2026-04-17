<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'invited_by', 'email', 'token', 'role', 'status', 'expires_at', 'accepted_at'])]
class OrganizationInvite extends Model
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
     * Get the user who sent the invite
     */
    public function invitedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'invited_by');
    }

    /**
     * Check if invite is expired
     */
    public function isExpired(): bool
    {
        if (! $this->expires_at) {
            return false;
        }

        return now()->isAfter($this->expires_at);
    }

    /**
     * Check if invite is still pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending' && ! $this->isExpired();
    }
}
