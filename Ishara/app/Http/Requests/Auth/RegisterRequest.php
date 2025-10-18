<?php

namespace App\Http\Requests\Website\Auth;

use Illuminate\Foundation\Http\FormRequest;

class RegisterRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'gender' => 'required|in:male,female',
            'phone' => 'required|string|max:20',
            'email' => 'required|email|max:255',
            'parent_name' => 'required|string|max:255',
            'parent_number' => 'required|string|max:20',
            'password' => 'required|string|min:8|confirmed',
        ];
    }
} 