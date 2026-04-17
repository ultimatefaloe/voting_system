<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class StoreElectionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true; // Policy handles authorization
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'organization_id' => [
                'required',
                'integer',
                'exists:organizations,id',
            ],
            'title' => [
                'required',
                'string',
                'min:3',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:2000',
            ],
            'type' => [
                'required',
                'string',
                'in:public,private',
            ],
            'start_date' => [
                'required',
                'date',
                'after_or_equal:now',
            ],
            'end_date' => [
                'required',
                'date',
                'after:start_date',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'organization_id.required' => 'Organization is required',
            'organization_id.exists' => 'The selected organization does not exist',
            'title.required' => 'Election title is required',
            'title.min' => 'Election title must be at least 3 characters',
            'title.max' => 'Election title cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            'type.required' => 'Election type is required',
            'type.in' => 'Election type must be either public or private',
            'start_date.required' => 'Start date is required',
            'start_date.after_or_equal' => 'Start date must be in the future',
            'end_date.required' => 'End date is required',
            'end_date.after' => 'End date must be after start date',
        ];
    }
}
