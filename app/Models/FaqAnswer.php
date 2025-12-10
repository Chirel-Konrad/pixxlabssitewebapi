<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="FaqAnswer",
 *     title="FaqAnswer",
 *     description="Réponse FAQ",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="faq_id", type="integer", example=1),
 *     @OA\Property(property="answer", type="string", example="Voici la réponse..."),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class FaqAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['faq_id', 'answer'];

    public function faq()
    {
        return $this->belongsTo(Faq::class);
    }
}


