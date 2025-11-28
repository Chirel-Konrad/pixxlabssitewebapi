<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UpdateNewsletterRequest extends FormRequest
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
        $newsletterId = $this->route('newsletter') ? $this->route('newsletter')->id : null;
        return [
            'email' => 'required|email|unique:newsletters,email,' . $newsletterId . ',id|max:255',
        ];
    }
}
