<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
/**
 * @OA\Schema(
 *     schema="BlogComment",
 *     title="BlogComment",
 *     description="Commentaire de blog",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="blog_id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="comment", type="string", example="Super article !"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */
class BlogComment extends Model
{
    use HasFactory;
    protected $fillable = ["blog_id", "user_id", "comment"];

      public function blog()
    {
        return $this->belongsTo(Blog::class);
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}




  