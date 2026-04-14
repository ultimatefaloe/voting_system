<?php

namespace Database\Seeders;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\ElectionAccess;
use App\Models\Organization;
use App\Models\OrganizationMember;
use App\Models\Position;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create demo users
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@voting.local',
        ]);

        $member1 = User::factory()->create([
            'name' => 'Member One',
            'email' => 'member1@voting.local',
        ]);

        $member2 = User::factory()->create([
            'name' => 'Member Two',
            'email' => 'member2@voting.local',
        ]);

        // Create demo organization
        $organization = Organization::factory()->create([
            'name' => 'Demo Organization',
            'slug' => 'demo-org',
            'owner_id' => $adminUser->id,
        ]);

        // Add members to organization
        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $adminUser->id,
            'role' => 'owner',
            'status' => 'active',
        ]);

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $member1->id,
            'role' => 'admin',
            'status' => 'active',
        ]);

        OrganizationMember::create([
            'organization_id' => $organization->id,
            'user_id' => $member2->id,
            'role' => 'member',
            'status' => 'active',
        ]);

        // Create sample elections
        $activeElection = Election::factory()->active()->create([
            'organization_id' => $organization->id,
            'title' => 'Board Elections 2025',
            'description' => 'Annual board member elections',
            'type' => 'private',
            'created_by' => $adminUser->id,
        ]);

        $draftElection = Election::factory()->create([
            'organization_id' => $organization->id,
            'title' => 'Committee Elections',
            'description' => 'Committee leadership elections',
            'type' => 'public',
            'created_by' => $adminUser->id,
        ]);

        // Create positions for active election
        $presidentPosition = Position::factory()->create([
            'election_id' => $activeElection->id,
            'title' => 'President',
            'max_votes' => 1,
            'order' => 1,
        ]);

        $secretaryPosition = Position::factory()->create([
            'election_id' => $activeElection->id,
            'title' => 'Secretary',
            'max_votes' => 1,
            'order' => 2,
        ]);

        $treasurerPosition = Position::factory()->create([
            'election_id' => $activeElection->id,
            'title' => 'Treasurer',
            'max_votes' => 1,
            'order' => 3,
        ]);

        // Create candidates for president
        Candidate::factory()->create([
            'position_id' => $presidentPosition->id,
            'name' => 'Alice Johnson',
            'bio' => '15 years of leadership experience',
            'order' => 1,
        ]);

        Candidate::factory()->create([
            'position_id' => $presidentPosition->id,
            'name' => 'Bob Smith',
            'bio' => '10 years of management experience',
            'order' => 2,
        ]);

        Candidate::factory()->create([
            'position_id' => $presidentPosition->id,
            'name' => 'Carol Davis',
            'bio' => '8 years of team building',
            'order' => 3,
        ]);

        // Create candidates for secretary
        Candidate::factory()->create([
            'position_id' => $secretaryPosition->id,
            'name' => 'David Wilson',
            'bio' => 'Excellent communication skills',
            'order' => 1,
        ]);

        Candidate::factory()->create([
            'position_id' => $secretaryPosition->id,
            'name' => 'Emma Brown',
            'bio' => 'Detail-oriented professional',
            'order' => 2,
        ]);

        // Create candidates for treasurer
        Candidate::factory()->create([
            'position_id' => $treasurerPosition->id,
            'name' => 'Frank Miller',
            'bio' => 'Certified financial professional',
            'order' => 1,
        ]);

        Candidate::factory()->create([
            'position_id' => $treasurerPosition->id,
            'name' => 'Grace Lee',
            'bio' => 'Experienced in budget management',
            'order' => 2,
        ]);

        // Create voter access tokens for private election
        ElectionAccess::factory()->count(5)->noExpiry()->create([
            'election_id' => $activeElection->id,
        ]);

        // Create positions for draft election
        $draftPosition = Position::factory()->create([
            'election_id' => $draftElection->id,
            'title' => 'Committee Head',
            'max_votes' => 1,
            'order' => 1,
        ]);

        Candidate::factory()->create([
            'position_id' => $draftPosition->id,
            'name' => 'Henry Taylor',
            'order' => 1,
        ]);

        Candidate::factory()->create([
            'position_id' => $draftPosition->id,
            'name' => 'Iris Martinez',
            'order' => 2,
        ]);
    }
}
