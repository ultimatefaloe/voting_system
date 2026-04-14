<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class UpdateElectionRequest extends FormRequest
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
            'title' => [
                'sometimes',
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
            'start_date' => [
                'sometimes',
                'required',
                'date',
                'after_or_equal:now',
            ],
            'end_date' => [
                'sometimes',
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
            'title.required' => 'Election title is required',
            'title.min' => 'Election title must be at least 3 characters',
            'title.max' => 'Election title cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 2000 characters',
            'start_date.after_or_equal' => 'Start date must be in the future',
            'end_date.after' => 'End date must be after start date',
        ];
    }
}
