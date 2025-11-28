<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreWebinarRequest extends FormRequest
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
            "title" => "required|string|max:255",
            "description" => "nullable|string",
            "whose" => "required|string|max:255",
            "date" => "required|string|max:255",
            "time" => "required|string|max:255",
            "image" => "nullable|image|mimes:jpg,jpeg,png|max:2048",
            "video" => "nullable|file|mimes:mp4,mov,avi,webm|max:51200", // max 50MB
        ];
    }
}
