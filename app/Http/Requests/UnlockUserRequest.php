<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UnlockUserRequest extends FormRequest
{
    public function authorize()
    {
        // Only admin can do this; implement your own admin check
        return true;
    }

    public function rules()
    {
        return [
            'user_id' => 'required|exists:users,id',
        ];
    }
}