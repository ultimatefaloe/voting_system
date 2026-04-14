<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['name', 'slug', 'owner_id'])]
class Organization extends Model
{
    use HasFactory;

    /**
     * Get the owner of the organization
     */
    public function owner(): BelongsTo
    {
        return $this->belongsTo(User::class, 'owner_id');
    }

    /**
     * Get all members of the organization
     */
    public function members(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'organization_members',
            'organization_id',
            'user_id'
        )
            ->withPivot('role', 'status')
            ->withTimestamps();
    }

    /**
     * Get organization member pivot records
     */
    public function organizationMembers(): HasMany
    {
        return $this->hasMany(OrganizationMember::class);
    }

    /**
     * Get elections belonging to this organization
     */
    public function elections(): HasMany
    {
        return $this->hasMany(Election::class);
    }

    /**
     * Get pending invites for this organization
     */
    public function invites(): HasMany
    {
        return $this->hasMany(OrganizationInvite::class);
    }

    /**
     * Check if user is member of organization
     */
    public function hasMember(User $user): bool
    {
        return $this->members()->where('user_id', $user->id)->exists();
    }

    /**
     * Get user's role in organization
     */
    public function getUserRole(User $user): ?string
    {
        return $this->members()
            ->where('user_id', $user->id)
            ->first()
            ?->pivot
            ->role;
    }
}
