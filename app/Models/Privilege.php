<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Privilege",
 *     title="Privilege",
 *     description="Privilège ou avantage",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Accès Premium"),
 *     @OA\Property(property="slug", type="string", example="acces-premium"),
 *     @OA\Property(property="description", type="string", example="Description du privilège"),
 *     @OA\Property(property="image", type="string", example="privileges/image.jpg"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Privilege extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image', 'slug'];
}

