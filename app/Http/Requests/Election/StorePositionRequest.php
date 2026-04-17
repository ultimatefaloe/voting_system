<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class StorePositionRequest extends FormRequest
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
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'max_votes' => [
                'required',
                'integer',
                'min:1',
                'max:10',
            ],
            'order' => [
                'nullable',
                'integer',
                'min:0',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'title.required' => 'Position title is required',
            'title.min' => 'Position title must be at least 2 characters',
            'title.max' => 'Position title cannot exceed 255 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
            'max_votes.required' => 'Maximum votes is required',
            'max_votes.min' => 'At least 1 vote must be allowed',
            'max_votes.max' => 'Maximum 10 votes allowed per position',
            'order.min' => 'Order must be a positive integer',
        ];
    }
}
