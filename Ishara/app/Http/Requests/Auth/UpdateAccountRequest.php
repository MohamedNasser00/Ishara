<?php

namespace App\Http\Requests\Website\Auth;

use Illuminate\Foundation\Http\FormRequest;

class UpdateAccountRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'first_name' => 'sometimes|string|max:255',
            'last_name' => 'sometimes|string|max:255',
            'gender' => 'sometimes|in:male,female',
            'phone' => 'sometimes|string|max:20|unique:users,phone,' . auth()->id(),
            'email' => 'sometimes|email|max:255|unique:users,email,' . auth()->id(),
            'parent_name' => 'sometimes|string|max:255',
            'parent_number' => 'sometimes|string|max:20',
        ];
    }

    public function messages(): array
    {
        return [
            'first_name.string' => 'First name must be a string.',
            'first_name.max' => 'First name cannot exceed 255 characters.',
            'last_name.string' => 'Last name must be a string.',
            'last_name.max' => 'Last name cannot exceed 255 characters.',
            'gender.in' => 'Gender must be either male or female.',
            'phone.string' => 'Phone must be a string.',
            'phone.max' => 'Phone cannot exceed 20 characters.',
            'phone.unique' => 'This phone number is already taken.',
            'email.email' => 'Please provide a valid email address.',
            'email.max' => 'Email cannot exceed 255 characters.',
            'email.unique' => 'This email address is already taken.',
            'parent_name.string' => 'Parent name must be a string.',
            'parent_name.max' => 'Parent name cannot exceed 255 characters.',
            'parent_number.string' => 'Parent number must be a string.',
            'parent_number.max' => 'Parent number cannot exceed 20 characters.',
        ];
    }
} 