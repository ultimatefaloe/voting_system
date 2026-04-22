<?php

namespace App\Http\Controllers;

use App\Models\Election;
use Inertia\Inertia;
use Inertia\Response;

class BallotPageController extends Controller
{
    public function __invoke(Election $election): Response
    {
        return Inertia::render('voting/ballot', [
            'ballotPageData' => [
                'election' => [
                    'id' => $election->id,
                    'title' => $election->title,
                    'description' => $election->description,
                    'type' => $election->type,
                    'status' => $election->status,
                    'start_date' => $election->start_date?->toDateTimeString(),
                    'end_date' => $election->end_date?->toDateTimeString(),
                ],
            ],
        ]);
    }
}
