<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreFaqRequest extends FormRequest
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
            'type' => 'required|in:home,webinars,partner,AI',
            'question' => 'required|string|max:255',
            'description' => 'nullable|string|max:255',
            'answers' => 'required|array|min:1',
            'answers.*' => 'required|string|max:1000',
        ];
    }
}
