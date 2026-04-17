<?php

namespace App\Http\Controllers;

use App\Http\Resources\PositionResource;
use App\Models\Election;
use App\Models\Position;
use App\Models\Organization;
use App\Http\Requests\Election\StorePositionRequest;
use Illuminate\Http\JsonResponse;

class PositionController extends Controller
{
    /**
     * Display all positions for an election.
     *
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function index(Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('view', $election);

        $positions = $election->positions()
            ->with('candidates')
            ->orderBy('order')
            ->get();

        return response()->json([
            'message' => 'Positions retrieved successfully',
            'data' => PositionResource::collection($positions),
        ]);
    }

    /**
     * Store a newly created position.
     *
     * @param StorePositionRequest $request
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function store(StorePositionRequest $request, Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('create', [Position::class, $election]);

        try {
            // Can only add positions to draft elections
            if (!$election->isDraft()) {
                return response()->json([
                    'message' => 'Can only add positions to draft elections',
                ], 400);
            }

            $position = $election->positions()->create([
                'title' => $request->title,
                'description' => $request->description,
                'max_votes' => $request->max_votes,
                'order' => $request->order ?? $election->positions()->count(),
            ]);

            return response()->json([
                'message' => 'Position created successfully',
                'data' => new PositionResource($position),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create position',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a specific position.
     *
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @return JsonResponse
     */
    public function show(Organization $organization, Election $election, Position $position): JsonResponse
    {
        $this->authorize('view', $election);

        return response()->json([
            'message' => 'Position retrieved successfully',
            'data' => new PositionResource($position->load('candidates')),
        ]);
    }

    /**
     * Update a specific position (draft only).
     *
     * @param StorePositionRequest $request
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @return JsonResponse
     */
    public function update(StorePositionRequest $request, Organization $organization, Election $election, Position $position): JsonResponse
    {
        $this->authorize('update', $position);

        try {
            // Can only update positions in draft elections
            if (!$election->isDraft()) {
                return response()->json([
                    'message' => 'Can only update positions in draft elections',
                ], 400);
            }

            $position->update([
                'title' => $request->title ?? $position->title,
                'description' => $request->description ?? $position->description,
                'max_votes' => $request->max_votes ?? $position->max_votes,
                'order' => $request->order ?? $position->order,
            ]);

            return response()->json([
                'message' => 'Position updated successfully',
                'data' => [
                    'id' => $position->id,
                    'title' => $position->title,
                    'description' => $position->description,
                    'max_votes' => $position->max_votes,
                    'order' => $position->order,
                    'candidate_count' => $position->candidates->count(),
                    'created_at' => $position->created_at,
                    'updated_at' => $position->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update position',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific position (draft only).
     *
     * @param Organization $organization
     * @param Election $election
     * @param Position $position
     * @return JsonResponse
     */
    public function destroy(Organization $organization, Election $election, Position $position): JsonResponse
    {
        $this->authorize('delete', $position);

        try {
            // Can only delete positions in draft elections
            if (!$election->isDraft()) {
                return response()->json([
                    'message' => 'Can only delete positions in draft elections',
                ], 400);
            }

            $position->delete();

            return response()->json([
                'message' => 'Position deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete position',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
