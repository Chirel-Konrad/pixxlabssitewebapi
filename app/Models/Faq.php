<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Faq",
 *     title="Faq",
 *     description="Question fréquente",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="type", type="string", example="home"),
 *     @OA\Property(property="question", type="string", example="Comment ça marche ?"),
 *     @OA\Property(property="description", type="string", example="Explication générale"),
 *     @OA\Property(property="slug", type="string", example="comment-ca-marche"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Faq extends Model
{
    //
    use HasFactory;
     protected $fillable = [
        'type',        // home, webinars, partner, AI
        'question',
        'description', // facultatif, explication du type
        'slug',
    ];

    public function answers()
{
    return $this->hasMany(FaqAnswer::class);
}

}



