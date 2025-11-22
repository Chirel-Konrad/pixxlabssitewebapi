<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
/**
 * @OA\Schema(
 *     schema="Product",
 *     title="Product",
 *     description="Produit",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Produit A"),
 *     @OA\Property(property="slug", type="string", example="produit-a"),
 *     @OA\Property(property="description", type="string", example="Description du produit"),
 *     @OA\Property(property="price", type="number", format="float", example=99.99),
 *     @OA\Property(property="image", type="string", example="products/image.jpg"),
 *     @OA\Property(property="status", type="string", example="available"),
 *     @OA\Property(property="created_at", type="string", format="date-time"),
 *     @OA\Property(property="updated_at", type="string", format="date-time")
 * )
 */
class Product extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'description',
        'slug',
        'price',
        'image',
        'status',
    ];
}
