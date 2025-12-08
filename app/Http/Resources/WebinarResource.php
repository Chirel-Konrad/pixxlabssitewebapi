<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WebinarResource extends JsonResource
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
            'title' => $this->title,
            'description' => $this->description,
            'date' => $this->date,
            'time' => $this->time,
            'video' => $this->video_url,
            'image' => $image,
            'slug' => $this->slug,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
