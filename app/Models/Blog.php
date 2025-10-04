<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]
class Blog extends Model
{
    use HasFactory;
    protected $fillable = ["title", "content", "image", "user_id", "category", "slug"];

    public function user()
{
    return $this->belongsTo(User::class);
}

public function comments()
{
    return $this->hasMany(BlogComment::class);
}


}
