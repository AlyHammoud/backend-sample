<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'required|string',
            'email' => 'required|unique:users,email|email',
            'username' => 'required|unique:users,username|string',
            'password' => 'required|min:3|max:18|confirmed',
            'role_id' => 'required|exists:roles,id'
        ];
    }

    public function messages()
    {
        return [
            // 'password.required' => 'password is requierd',
            // 'password.confirmed' => 'password does not match',
        ];
    }
}
