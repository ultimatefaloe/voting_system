<?php

namespace App\Policies;

use App\Models\Organization;
use App\Models\User;

class OrganizationPolicy
{
    /**
     * Determine if user can view organization
     */
    public function view(User $user, Organization $organization): bool
    {
        return $organization->hasMember($user);
    }

    /**
     * Determine if user can create organizations
     */
    public function create(User $user): bool
    {
        return true;
    }

    /**
     * Determine if user can update organization
     */
    public function update(User $user, Organization $organization): bool
    {
        $role = $organization->getUserRole($user);

        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if user can delete organization
     */
    public function delete(User $user, Organization $organization): bool
    {
        return $organization->getUserRole($user) === 'owner';
    }

    /**
     * Determine if user can manage members
     */
    public function manageMember(User $user, Organization $organization): bool
    {
        $role = $organization->getUserRole($user);

        return in_array($role, ['owner', 'admin']);
    }

    /**
     * Determine if user can view analytics
     */
    public function viewAnalytics(User $user, Organization $organization): bool
    {
        $role = $organization->getUserRole($user);

        return in_array($role, ['owner', 'admin']);
    }
}
