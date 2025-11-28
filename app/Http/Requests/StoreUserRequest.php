<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreUserRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
            'phone' => 'nullable|string|max:20',
            'is_2fa_enable' => 'nullable|boolean',
            'provider' => 'nullable|string|max:255',
            'provider_id' => 'nullable|string|max:255',
            'status' => 'nullable|in:active,inactive,banned',
            'image' => 'nullable|string',
            'role' => 'nullable|in:user,admin,superadmin',
        ];
    }
}
