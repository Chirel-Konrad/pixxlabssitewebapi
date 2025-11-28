<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class WebinarRegistrationResource extends JsonResource
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
            'webinar_id' => $this->webinar_id,
            'name' => $this->name,
            'email' => $this->email,
            'slug' => $this->slug,
            'webinar' => new WebinarResource($this->whenLoaded('webinar')),
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
