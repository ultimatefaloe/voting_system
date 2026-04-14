<?php

namespace App\Http\Controllers;

use App\Models\Candidate;
use App\Models\Election;
use App\Models\Position;
use App\Models\Organization;
use App\Http\Requests\Election\StoreCandidateRequest;
use App\Http\Resources\CandidateResource;
use Illuminate\Http\JsonResponse;

class CandidateController extends Controller
{
    /**
     * Display all candidates for a position.
     *
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @return JsonResponse
     */
    public function index(Organization $organization, Election $election, Position $position): JsonResponse
    {
        $this->authorize('view', $election);

        $candidates = $position->candidates()
            ->orderBy('order')
            ->get();

        return response()->json([
            'message' => 'Candidates retrieved successfully',
            'data' => CandidateResource::collection($candidates),
        ]);
    }

    /**
     * Store a newly created candidate.
     *
     * @param StoreCandidateRequest $request
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @return JsonResponse
     */
    public function store(StoreCandidateRequest $request, Organization $organization, Election $election, Position $position): JsonResponse
    {
        $this->authorize('create', [Candidate::class, $election]);

        try {
            // Can only add candidates to draft elections
            if (!$election->isDraft()) {
                return response()->json([
                    'message' => 'Can only add candidates to draft elections',
                ], 400);
            }

            $candidate = $position->candidates()->create([
                'name' => $request->name,
                'bio' => $request->bio,
                'avatar' => $request->avatar,
                'order' => $request->order ?? $position->candidates()->count(),
            ]);

            return response()->json([
                'message' => 'Candidate created successfully',
                'data' => new CandidateResource($candidate),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create candidate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a specific candidate.
     *
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @param Candidate $candidate
     * @return JsonResponse
     */
    public function show(Organization $organization, Election $election, Position $position, Candidate $candidate): JsonResponse
    {
        $this->authorize('view', $election);

        return response()->json([
            'message' => 'Candidate retrieved successfully',
            'data' => new CandidateResource($candidate),
        ]);
    }

    /**
     * Update a specific candidate (draft only).
     *
     * @param StoreCandidateRequest $request
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @param Candidate $candidate
     * @return JsonResponse
     */
    public function update(StoreCandidateRequest $request, Organization $organization, Election $election, Position $position, Candidate $candidate): JsonResponse
    {
        $this->authorize('update', $candidate);

        try {
            // Can only update candidates in draft elections
            if (!$election->isDraft()) {
                return response()->json([
                    'message' => 'Can only update candidates in draft elections',
                ], 400);
            }

            $candidate->update([
                'name' => $request->name ?? $candidate->name,
                'bio' => $request->bio ?? $candidate->bio,
                'avatar' => $request->avatar ?? $candidate->avatar,
                'order' => $request->order ?? $candidate->order,
            ]);

            return response()->json([
                'message' => 'Candidate updated successfully',
                'data' => [
                    'id' => $candidate->id,
                    'name' => $candidate->name,
                    'bio' => $candidate->bio,
                    'avatar' => $candidate->avatar,
                    'order' => $candidate->order,
                    'created_at' => $candidate->created_at,
                    'updated_at' => $candidate->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update candidate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific candidate (draft only).
     *
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @param Candidate $candidate
     * @return JsonResponse
     */
    public function destroy(Organization $organization, Election $election, Position $position, Candidate $candidate): JsonResponse
    {
        $this->authorize('delete', $candidate);

        try {
            // Can only delete candidates in draft elections
            if (!$election->isDraft()) {
                return response()->json([
                    'message' => 'Can only delete candidates in draft elections',
                ], 400);
            }

            $candidate->delete();

            return response()->json([
                'message' => 'Candidate deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete candidate',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
