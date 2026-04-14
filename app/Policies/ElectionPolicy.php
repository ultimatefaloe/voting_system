<?php

namespace App\Policies;

use App\Models\Election;
use App\Models\User;

class ElectionPolicy
{
    /**
     * Determine if user can view election
     */
    public function view(User $user, Election $election): bool
    {
        // Check if user is member of the organization
        return $election->organization->hasMember($user);
    }

    /**
     * Determine if user can create election
     */
    public function create(User $user, Election $election = null): bool
    {
        if ($election === null) {
            return true;
        }

        return $election->organization->hasMember($user);
    }

    /**
     * Determine if user can update election
     */
    public function update(User $user, Election $election): bool
    {
        if ($election->status !== 'draft') {
            return false;
        }

        $role = $election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can delete election
     */
    public function delete(User $user, Election $election): bool
    {
        if ($election->status !== 'draft') {
            return false;
        }

        $role = $election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if user can start election
     */
    public function start(User $user, Election $election): bool
    {
        $role = $election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can stop election
     */
    public function stop(User $user, Election $election): bool
    {
        $role = $election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can publish election results
     */
    public function publish(User $user, Election $election): bool
    {
        $role = $election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if user can view results
     */
    public function viewResults(User $user, Election $election): bool
    {
        $role = $election->organization->getUserRole($user);

        // Only org members can view results
        return $role !== null;
    }

    /**
     * Determine if user can manage access tokens
     */
    public function manageAccess(User $user, Election $election): bool
    {
        $role = $election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin']);
    }
}
