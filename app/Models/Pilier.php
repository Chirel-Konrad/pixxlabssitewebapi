<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
/**
 * @OA\Schema(
 *     schema="Pilier",
 *     title="Pilier",
 *     description="Pilier de l'entreprise",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Innovation"),
 *     @OA\Property(property="slug", type="string", example="innovation"),
 *     @OA\Property(property="description", type="string", example="Notre engagement..."),
 *     @OA\Property(property="image", type="string", example="piliers/image.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Pilier extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image', 'slug'];
}
