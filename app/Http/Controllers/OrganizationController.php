<?php

namespace App\Http\Controllers;

use App\Http\Resources\OrganizationResource;
use App\Models\Organization;
use App\Http\Requests\Organization\StoreOrganizationRequest;
use App\Http\Requests\Organization\UpdateOrganizationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class OrganizationController extends Controller
{
    /**
     * Display a listing of organizations.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        $organizations = Auth::user()->organizations()->with('members')->get();

        return response()->json([
            'message' => 'Organizations retrieved successfully',
            'data' => OrganizationResource::collection($organizations),
        ]);
    }

    /**
     * Store a newly created organization.
     *
     * @param StoreOrganizationRequest $request
     * @return JsonResponse
     */
    public function store(StoreOrganizationRequest $request): JsonResponse
    {
        try {
            $organization = Organization::create([
                'name' => $request->name,
                'slug' => $request->slug,
                'description' => $request->description,
                'owner_id' => Auth::id(),
            ]);

            // Add creator as owner
            $organization->members()->attach(Auth::id(), [
                'role' => 'owner',
                'status' => 'active',
            ]);

            return response()->json([
                'message' => 'Organization created successfully',
                'data' => new OrganizationResource($organization),
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to create organization',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display a specific organization.
     *
     * @param Organization $organization
     * @return JsonResponse
     */
    public function show(Organization $organization): JsonResponse
    {
        $this->authorize('view', $organization);

        return response()->json([
            'message' => 'Organization retrieved successfully',
            'data' => new OrganizationResource($organization->load('members')),
        ]);
    }

    /**
     * Update a specific organization.
     *
     * @param UpdateOrganizationRequest $request
     * @param Organization $organization
     * @return JsonResponse
     */
    public function update(UpdateOrganizationRequest $request, Organization $organization): JsonResponse
    {
        $this->authorize('update', $organization);

        try {
            $organization->update($request->validated());

            return response()->json([
                'message' => 'Organization updated successfully',
                'data' => new OrganizationResource($organization),
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to update organization',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Delete a specific organization.
     *
     * @param Organization $organization
     * @return JsonResponse
     */
    public function destroy(Organization $organization): JsonResponse
    {
        $this->authorize('delete', $organization);

        try {
            $organization->delete();

            return response()->json([
                'message' => 'Organization deleted successfully',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Failed to delete organization',
                'error' => $e->getMessage(),
            ], 500);
        }
    }
}
