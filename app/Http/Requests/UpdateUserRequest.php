<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
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
        $userId = $this->route('user') ? $this->route('user')->id : null;

        return [
            'name' => 'sometimes|required|string|max:255',
            'email' => [
                'sometimes',
                'required',
                'email',
                \Illuminate\Validation\Rule::unique('users')->ignore($userId)
            ],
            'password' => 'nullable|string|min:8',
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
