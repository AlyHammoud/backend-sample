<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;

class UpdateUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        if (Gate::allows('updateUser', $this->user)) {
            return true;
        }
        return false;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'name' => 'sometimes|required|string',
            'email' => "sometimes|required|unique:users,email,{$this->user->id},id|email",
            'username' => "sometimes|required|unique:users,username,{$this->user->id},id|string",
            'password' => 'sometimes|min:3|max:18|confirmed',
            'role_id' => 'sometimes|exists:roles,id'
        ];
    }
}
