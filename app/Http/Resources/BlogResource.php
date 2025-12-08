<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'content' => $this->content,
            'image' => $this->image
                ? (Str::startsWith($this->image, ['http://', 'https://', '//'])
                    ? $this->image
                    : url(Storage::url($this->image)))
                : null,
            'category' => $this->category,
            'author' => new UserResource($this->whenLoaded('user')),
            'comments' => BlogCommentResource::collection($this->whenLoaded('comments')),
            'created_at' => $this->created_at->toIso8601String(),
            'updated_at' => $this->updated_at->toIso8601String(),
        ];
    }
}
