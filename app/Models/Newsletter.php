<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
/**
 * @OA\Schema(
 *     schema="Newsletter",
 *     title="Newsletter",
 *     description="Abonné Newsletter",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="email", type="string", format="email", example="user@example.com"),
 *     @OA\Property(property="slug", type="string", example="user-example-com"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Newsletter extends Model
{
    use HasFactory;
    protected $fillable = ["email", "slug"];
}
