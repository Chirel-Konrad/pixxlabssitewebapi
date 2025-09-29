<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Webinar;
use Illuminate\Support\Str;

class WebinarSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Webinar::create([
            'title' => 'Découvrir Piixlabs',
            'description' => 'Un tour complet de la plateforme, ses produits, ses valeurs et son ambition pour le digital africain.',
            'video_url'   => 'https://exemple.com/video.mp4', // ✅ mettre un lien valide ou temporaire
            'slug' => Str::slug('Découvrir Piixlabs') . '-' . uniqid(),
            'whose' => 'utilisateurs curieux, nouveaux inscrits',
            'date' => 'Mardi',
            'time' => '18H GMT',
           'image' => 'https://exemple.com/image.jpg', // ✅ ou un chemin d’image
            'created_at'  => now(),
            'updated_at'  => now(),
        ]);
    }
}
