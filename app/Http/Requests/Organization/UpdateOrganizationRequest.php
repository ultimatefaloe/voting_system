<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class UpdateOrganizationRequest extends FormRequest
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
        $organizationId = $this->route('organization') ?? $this->route('id');

        return [
            'name' => [
                'sometimes',
                'required',
                'string',
                'min:2',
                'max:255',
            ],
            'slug' => [
                'sometimes',
                'required',
                'string',
                'lowercase',
                'regex:/^[a-z0-9]+(?:-[a-z0-9]+)*$/',
                'unique:organizations,slug,' . $organizationId,
                'min:2',
                'max:100',
            ],
            'description' => [
                'nullable',
                'string',
                'max:1000',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'name.required' => 'Organization name is required',
            'name.min' => 'Organization name must be at least 2 characters',
            'name.max' => 'Organization name cannot exceed 255 characters',
            'slug.required' => 'Organization slug is required',
            'slug.regex' => 'Slug can only contain lowercase letters, numbers, and hyphens',
            'slug.unique' => 'This slug is already taken',
            'slug.min' => 'Slug must be at least 2 characters',
            'slug.max' => 'Slug cannot exceed 100 characters',
            'description.max' => 'Description cannot exceed 1000 characters',
        ];
    }
}
