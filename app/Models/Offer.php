<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
/**
 * @OA\Schema(
 *     schema="Offer",
 *     title="Offer",
 *     description="Offre commerciale",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Offre Spéciale"),
 *     @OA\Property(property="slug", type="string", example="offre-speciale"),
 *     @OA\Property(property="description", type="string", example="Détails de l'offre"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Offer extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'slug'];
}
