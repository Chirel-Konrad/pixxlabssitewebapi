<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
/**
 * @OA\Schema(
 *     schema="Testimonial",
 *     title="Testimonial",
 *     description="Témoignage utilisateur",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=1),
 *     @OA\Property(property="content", type="string", example="Excellent service !"),
 *     @OA\Property(property="slug", type="string", example="excellent-service"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time"),
 *     @OA\Property(property="user", ref="#/components/schemas/User")
 * )
 */
class Testimonial extends Model
{
    use HasFactory;

    
    protected $fillable = ["user_id", "content", "slug"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}

