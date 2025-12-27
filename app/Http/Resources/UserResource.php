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
            'slug' => $this->slug,
            'email' => $this->email,
            'phone' => $this->phone,
            'image' => $this->image
                ? (Str::startsWith($this->image, ['http://', 'https://', '//'])
                    ? $this->image
                    : url(Storage::url($this->image)))
                : null,
            'role' => $this->role,
            'status' => $this->status,
             'is_2fa_enable' => (bool) $this->is_2fa_enable,
            'email_verified_at' => $this->email_verified_at?->toIso8601String(),
            'provider' => $this->provider,
            'provider_id' => $this->provider_id,
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}
