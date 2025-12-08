<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class TestimonialResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = null;
        if ($this->image) {
            $image = Str::startsWith($this->image, ['http://', 'https://', '//'])
                ? $this->image
                : url(Storage::url($this->image));
        }
        return [
            'id' => $this->id,
            'name' => $this->name,
            'role' => $this->role,
            'content' => $this->content,
            'image' => $image,
            'slug' => $this->slug,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
