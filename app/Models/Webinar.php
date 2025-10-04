<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
class Webinar extends Model
{
    use HasFactory;

    protected $fillable = ["title", "description", "video_url", "slug"];

    public function webinarRegistrations()
    {
        return $this->hasMany(WebinarRegistration::class);
    }
    public function users()
{
    return $this->belongsToMany(User::class, 'webinar_registrations');
}
   
}


   


