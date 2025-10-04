<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]

class Pilier extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'description', 'image', 'slug'];
}
