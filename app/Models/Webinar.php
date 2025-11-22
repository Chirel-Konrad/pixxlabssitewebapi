<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
/**
 * @OA\Schema(
 *     schema="Webinar",
 *     title="Webinar",
 *     description="Webinaire",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Introduction à Laravel"),
 *     @OA\Property(property="slug", type="string", example="introduction-a-laravel"),
 *     @OA\Property(property="description", type="string", example="Description du webinaire"),
 *     @OA\Property(property="video_url", type="string", example="https://youtube.com/watch?v=..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Webinar extends Model
{
    use HasFactory;

    protected $fillable = ["title", "description", "video_url", "slug"];

    public function webinarRegistrations()
    {
        return $this->hasMany(WebinarRegistration::class);
    }
    public function users()
{
    return $this->belongsToMany(User::class, 'webinar_registrations');
}
   
}


   


