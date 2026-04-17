<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MeController extends Controller
{
    /**
     * Get authenticated user
     */
    public function show(Request $request): UserResource
    {
        return new UserResource($request->user());
    }
}
