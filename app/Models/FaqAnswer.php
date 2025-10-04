<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use ApiPlatform\Metadata\ApiResource;
#[ApiResource]

class FaqAnswer extends Model
{
    use HasFactory;

    protected $fillable = ['faq_id', 'answer'];

    public function faq()
    {
        return $this->belongsTo(Faq::class);
    }
}


