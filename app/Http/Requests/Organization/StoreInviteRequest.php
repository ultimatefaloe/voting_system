<?php

namespace App\Http\Requests\Organization;

use Illuminate\Foundation\Http\FormRequest;

class StoreInviteRequest extends FormRequest
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
            'email' => [
                'required',
                'email',
            ],
            'role' => [
                'required',
                'string',
                'in:admin,member,viewer',
            ],
            'expires_at' => [
                'nullable',
                'date',
                'after_or_equal:now',
            ],
        ];
    }

    /**
     * Get custom messages for validator errors.
     */
    public function messages(): array
    {
        return [
            'email.required' => 'Email address is required',
            'email.email' => 'Please provide a valid email address',
            'role.required' => 'Role is required',
            'role.in' => 'Role must be one of: admin, member, viewer',
            'expires_at.date' => 'Expiration date must be a valid date',
            'expires_at.after_or_equal' => 'Expiration date must be in the future',
        ];
    }
}
