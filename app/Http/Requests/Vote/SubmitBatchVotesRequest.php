<?php

namespace App\Http\Requests\Vote;

use Illuminate\Foundation\Http\FormRequest;

class SubmitBatchVotesRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Voter token validation happens in middleware
    }

    public function rules(): array
    {
        return [
            'votes' => 'required|array|min:1',
            'votes.*.position_id' => 'required|integer|exists:positions,id',
            'votes.*.candidate_id' => 'required|integer|exists:candidates,id',
        ];
    }

    public function messages(): array
    {
        return [
            'votes.required' => 'Votes array is required',
            'votes.array' => 'Votes must be an array',
            'votes.min' => 'At least one vote is required',
            'votes.*.position_id.required' => 'Position ID is required for each vote',
            'votes.*.position_id.integer' => 'Position ID must be an integer',
            'votes.*.position_id.exists' => 'Invalid position ID',
            'votes.*.candidate_id.required' => 'Candidate ID is required for each vote',
            'votes.*.candidate_id.integer' => 'Candidate ID must be an integer',
            'votes.*.candidate_id.exists' => 'Invalid candidate ID',
        ];
    }
}
