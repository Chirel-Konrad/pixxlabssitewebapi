<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateFaqRequest extends FormRequest
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
            'type' => 'sometimes|in:home,webinars,partner,AI',
            'question' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:255',
            'answers' => 'sometimes|array',
            'answers.*' => 'required|string|max:1000',
        ];
    }
}
