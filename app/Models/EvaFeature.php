<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="EvaFeature",
 *     title="EvaFeature",
 *     description="Fonctionnalité EVA",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="title", type="string", example="Analyse prédictive"),
 *     @OA\Property(property="slug", type="string", example="analyse-predictive"),
 *     @OA\Property(property="description", type="string", example="Description de la fonctionnalité"),
 *     @OA\Property(property="logo", type="string", example="features/logo.png"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class EvaFeature extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'logo', 'slug'];
}
