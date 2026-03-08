<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRequest extends FormRequest
{
    // Authorize all requests for now (internal tool)
    public function authorize()
    {
        return true;
    }

    // Validation rules
    public function rules()
    {
        // You can switch rules depending on route or action if needed
        return [
            'email' => 'required|email|max:255',
            'password' => 'required|string|min:8', 
            'must_change_password' => 'sometimes|boolean',
        ];
    }

    // Optional: custom messages
    public function messages()
    {
        return [
            'email.required' => 'Email is required.',
            'email.email' => 'Invalid email format.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
        ];
    }
}