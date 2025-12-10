<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Blog",
 *     title="Blog",
 *     description="Article de blog",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Mon premier article"),
 *     @OA\Property(property="slug", type="string", example="mon-premier-article"),
 *     @OA\Property(property="content", type="string", example="<p>Contenu...</p>"),
 *     @OA\Property(property="image", type="string", example="uploads/blogs/image.jpg"),
 *     @OA\Property(property="category", type="string", example="Technologie"),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */
class Blog extends Model
{
    use HasFactory;
    protected $fillable = ["title", "content", "image", "user_id", "category", "slug"];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function comments()
{
    return $this->hasMany(BlogComment::class);
}


}
