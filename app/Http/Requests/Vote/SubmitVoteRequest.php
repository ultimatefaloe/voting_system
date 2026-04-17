<?php

namespace App\Http\Requests\Vote;

use Illuminate\Foundation\Http\FormRequest;

class SubmitVoteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Voter token validation happens in middleware
    }

    public function rules(): array
    {
        return [
            'position_id' => 'required|integer|exists:positions,id',
            'candidate_id' => 'required|integer|exists:candidates,id',
        ];
    }

    public function messages(): array
    {
        return [
            'position_id.required' => 'Position ID is required',
            'position_id.integer' => 'Position ID must be an integer',
            'position_id.exists' => 'Invalid position ID',
            'candidate_id.required' => 'Candidate ID is required',
            'candidate_id.integer' => 'Candidate ID must be an integer',
            'candidate_id.exists' => 'Invalid candidate ID',
        ];
    }
}
