<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BlogCommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $user = $this->whenLoaded('user') ?? $this->user;

        $image = null;
        if ($user && $user->image) {
            $image = Str::startsWith($user->image, ['http://', 'https://', '//'])
                ? $user->image
                : url(Storage::url($user->image));
        }

        return [
            'id' => $this->id,
            'name' => optional($user)->name,
            'email' => optional($user)->email,
            'image' => $image,
            'comment' => $this->comment,
            'created_at' => $this->created_at->toIso8601String(),
        ];
    }
}
