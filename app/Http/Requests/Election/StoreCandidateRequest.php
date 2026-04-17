<?php

namespace App\Http\Requests\Election;

use Illuminate\Foundation\Http\FormRequest;

class StoreCandidateRequest extends FormRequest
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
            'name' => [
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'bio' => [
                'nullable',
                'string',
                'max:1000',
            ],
            'avatar' => [
                'nullable',
                'url',
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
            'name.required' => 'Candidate name is required',
            'name.min' => 'Candidate name must be at least 2 characters',
            'name.max' => 'Candidate name cannot exceed 255 characters',
            'bio.max' => 'Biography cannot exceed 1000 characters',
            'avatar.url' => 'Avatar must be a valid URL',
            'order.min' => 'Order must be a positive integer',
        ];
    }
}
