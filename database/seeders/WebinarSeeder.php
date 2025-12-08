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
        // Jeux d'images Unsplash (catégories génériques)
        $images = [
            'https://images.unsplash.com/photo-1522075469751-3a6694fb2f61',
            'https://images.unsplash.com/photo-1519389950473-47ba0277781c',
            'https://images.unsplash.com/photo-1556761175-5973dc0f32e7',
            'https://images.unsplash.com/photo-1521737604893-d14cc237f11d',
            'https://images.unsplash.com/photo-1523246203453-1f2a5621e8f7'
        ];

        // Vidéos Pexels légères (<= 720p, durées courtes)
        $videos = [
            'https://videos.pexels.com/video-files/856904/856904-hd_1280_720_30fps.mp4',
            'https://videos.pexels.com/video-files/3156382/3156382-hd_1280_720_30fps.mp4',
            'https://videos.pexels.com/video-files/3184287/3184287-hd_1280_720_25fps.mp4',
            'https://videos.pexels.com/video-files/27758990/12899319_2560_1440_60fps.mp4',
            'https://videos.pexels.com/video-files/3015491/3015491-hd_1280_720_25fps.mp4'
        ];

        $webinars = [
            [
                'title' => 'Découvrir Piixlabs',
                'description' => 'Un tour complet de la plateforme, ses produits, ses valeurs et son ambition pour le digital africain.',
                'whose' => 'utilisateurs curieux, nouveaux inscrits',
                'date' => 'Mardi',
                'time' => '18H GMT',
            ],
            [
                'title' => 'Introduction à la création de contenu numérique',
                'description' => 'Apprenez les bases pour créer du contenu numérique captivant et professionnel avec Piixlabs.',
                'whose' => 'créateurs, influenceurs débutants',
                'date' => 'Jeudi',
                'time' => '17H GMT',
            ],
            [
                'title' => 'Marketing digital pour débutants',
                'description' => 'Comprenez les stratégies clés pour promouvoir vos produits ou services sur les réseaux numériques.',
                'whose' => 'entrepreneurs, marketeurs, étudiants',
                'date' => 'Samedi',
                'time' => '16H GMT',
            ],
        ];

        foreach ($webinars as $data) {
            Webinar::create([
                'title' => $data['title'],
                'description' => $data['description'],
                'video_url' => $videos[array_rand($videos)],
                'slug' => Str::slug($data['title']) . '-' . uniqid(),
                'whose' => $data['whose'],
                'date' => $data['date'],
                'time' => $data['time'],
                'image' => $images[array_rand($images)],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
