<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['organization_id', 'user_id', 'role', 'status'])]
class OrganizationMember extends Model
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
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Check if member has admin or higher role
     */
    public function isAdmin(): bool
    {
        return in_array($this->role, ['owner', 'admin']);
    }

    /**
     * Check if member has owner role
     */
    public function isOwner(): bool
    {
        return $this->role === 'owner';
    }

    /**
     * Check if member has viewer role
     */
    public function isViewer(): bool
    {
        return $this->role === 'viewer';
    }
}
