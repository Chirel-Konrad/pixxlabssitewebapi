<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'image' => $this->image
                ? (Str::startsWith($this->image, ['http://', 'https://', '//'])
                    ? $this->image
                    : url(Storage::url($this->image)))
                : null,
            'role' => $this->role,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
