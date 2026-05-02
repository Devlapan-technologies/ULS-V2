<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CreateUserRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'email' => 'required|email|unique:users,email|ends_with:@devlapan.com',
            'password' => 'required|string|min:8',
            'employee_id' => 'required|integer',
            'user_id' => 'nullable|integer|unique:users,id',   // allow manual ID (must not exist)
        ];
    }

    public function messages()
    {
        return [
            'email.ends_with' => 'Only @devlapan.com email addresses are allowed. Please use a company email.',
            'email.unique' => 'This email address is already registered.',
            'email.required' => 'Email address is required.',
            'email.email' => 'Please enter a valid email address.',
            'password.required' => 'Password is required.',
            'password.min' => 'Password must be at least 8 characters.',
            'employee_id.required' => 'Employee ID is required.',
            'user_id.unique' => 'The provided user ID already exists.',
        ];
    }
}