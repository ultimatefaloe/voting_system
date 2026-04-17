<?php

namespace App\Http\Controllers;

use App\Http\Resources\ElectionResource;
use App\Models\Election;
use App\Models\Organization;
use App\Http\Requests\Election\StoreElectionRequest;
use App\Http\Requests\Election\UpdateElectionRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class ElectionController extends Controller
{
    /**
     * Display all elections for an organization.
     *
     * @param Organization $organization
     * @return JsonResponse
     */
    public function index(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        $elections = $organization->elections()
            ->with('positions.candidates', 'createdBy')
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json([
            'message' => 'Elections retrieved successfully',
            'data' => ElectionResource::collection($elections),
        ]);
    }

    /**
     * Store a newly created election.
     *
     * @param StoreElectionRequest $request
     * @param Organization $organization
     * @return JsonResponse
     */
    public function store(StoreElectionRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('create', Election::class);

        try {
            $election = $organization->elections()->create([
                'title' => $request->title,
                'description' => $request->description,
                'type' => $request->type,
                'status' => 'draft',
                'start_date' => $request->start_date,
                'end_date' => $request->end_date,
                'created_by' => Auth::id(),
            ]);

            return response()->json([
                'message' => 'Election created successfully',
                'data' => new ElectionResource($election),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create election',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a specific election.
     *
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function show(Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('view', $election);

        return response()->json([
            'message' => 'Election retrieved successfully',
            'data' => new ElectionResource($election->load('positions.candidates')),
        ]);
    }

    /**
     * Update a specific election (draft only).
     *
     * @param UpdateElectionRequest $request
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function update(UpdateElectionRequest $request, Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('update', $election);

        try {
            $election->update($request->validated());

            return response()->json([
                'message' => 'Election updated successfully',
                'data' => [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'type' => $election->type,
                    'status' => $election->status,
                    'start_date' => $election->start_date,
                    'end_date' => $election->end_date,
                    'position_count' => $election->positions->count(),
                    'created_at' => $election->created_at,
                    'updated_at' => $election->updated_at,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update election',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific election (draft only).
     *
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function destroy(Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('delete', $election);

        try {
            $election->delete();

            return response()->json([
                'message' => 'Election deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete election',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Start an election (transition from draft to active).
     *
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function start(Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('start', $election);

        try {
            if (!$election->canStart()) {
                return response()->json([
                    'message' => 'Election cannot be started. Must be in draft status and have positions.',
                ], 400);
            }

            $election->update(['status' => 'active']);

            return response()->json([
                'message' => 'Election started successfully',
                'data' => [
                    'id' => $election->id,
                    'title' => $election->title,
                    'status' => $election->status,
                    'start_date' => $election->start_date,
                    'end_date' => $election->end_date,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to start election',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Stop an election (transition from active to stopped).
     *
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function stop(Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('stop', $election);

        try {
            if (!$election->canStop()) {
                return response()->json([
                    'message' => 'Election cannot be stopped. Must be in active status.',
                ], 400);
            }

            $election->update(['status' => 'stopped']);

            return response()->json([
                'message' => 'Election stopped successfully',
                'data' => [
                    'id' => $election->id,
                    'title' => $election->title,
                    'status' => $election->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to stop election',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Publish an election (transition to published).
     *
     * @param Organization $organization
     * @param Election $election
     * @return JsonResponse
     */
    public function publish(Organization $organization, Election $election): JsonResponse
    {
        $this->authorize('publish', $election);

        try {
            if (!$election->canPublish()) {
                return response()->json([
                    'message' => 'Election cannot be published. Must be stopped or closed.',
                ], 400);
            }

            $election->update(['status' => 'published']);

            return response()->json([
                'message' => 'Election published successfully',
                'data' => [
                    'id' => $election->id,
                    'title' => $election->title,
                    'status' => $election->status,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to publish election',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
