<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class EvaFeatureResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $image = null;
        if ($this->logo) {
            $image = Str::startsWith($this->logo, ['http://', 'https://', '//'])
                ? $this->logo
                : url(Storage::url($this->logo));
        }
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'image' => $image,
            'slug' => $this->slug,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
