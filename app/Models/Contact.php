<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]

class Contact extends Model
{
    use HasFactory;

    protected $fillable = ["firstname", "lastname", "email", "message", "slug"];
}





