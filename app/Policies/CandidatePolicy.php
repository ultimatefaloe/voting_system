<?php

namespace App\Policies;

use App\Models\Candidate;
use App\Models\User;

class CandidatePolicy
{
    /**
     * Determine if user can view candidate
     */
    public function view(User $user, Candidate $candidate): bool
    {
        return $candidate->position->election->organization->hasMember($user);
    }

    /**
     * Determine if user can create candidate
     */
    public function create(User $user, Candidate $candidate = null): bool
    {
        if ($candidate === null) {
            return true;
        }

        $role = $candidate->position->election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can update candidate
     */
    public function update(User $user, Candidate $candidate): bool
    {
        if ($candidate->position->election->status !== 'draft') {
            return false;
        }

        $role = $candidate->position->election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }

    /**
     * Determine if user can delete candidate
     */
    public function delete(User $user, Candidate $candidate): bool
    {
        if ($candidate->position->election->status !== 'draft') {
            return false;
        }

        $role = $candidate->position->election->organization->getUserRole($user);

        return in_array($role, ['owner', 'admin', 'member']);
    }
}
