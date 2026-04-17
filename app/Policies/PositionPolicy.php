<?php

namespace App\Policies;

use App\Models\Position;
use App\Models\User;

class PositionPolicy
{
    /**
     * Determine if user can view position
     */
    public function view(User $user, Position $position): bool
    {
        return $position->election->organization->hasMember($user);
    }

    /**
     * Determine if user can create position
     */
    public function create(User $user, Position $position = null): bool
    {
        if ($position === null) {
            return true;
        }

        $role = $position->election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can update position
     */
    public function update(User $user, Position $position): bool
    {
        if ($position->election->status !== 'draft') {
            return false;
        }

        $role = $position->election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can delete position
     */
    public function delete(User $user, Position $position): bool
    {
        if ($position->election->status !== 'draft') {
            return false;
        }

        $role = $position->election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }
}
