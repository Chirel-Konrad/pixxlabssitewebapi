<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
class Testimonial extends Model
{
    use HasFactory;

    
    protected $fillable = ["user_id", "content", "slug"];

    public function user()
    {
        return $this->belongsTo(User::class);
    }



}

