<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]

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



