<?php
namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Blog;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class BlogSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $userIds = User::pluck('id')->toArray();

        // Catégories avec du contenu réaliste
        $categories = [
            'Technologie',
            'Business',
            'Voyage',
            'Santé',
            'Développement personnel',
            'Lifestyle',
            'Culture',
            'Éducation',
            'Divertissement',
            
        ];

        // Images réalistes depuis Unsplash (libres d’utilisation)
        $images = [
            'https://source.unsplash.com/640x480/?technology',
            'https://source.unsplash.com/640x480/?business',
            'https://source.unsplash.com/640x480/?travel',
            'https://source.unsplash.com/640x480/?health',
            'https://source.unsplash.com/640x480/?education',
            'https://source.unsplash.com/640x480/?nature',
            'https://source.unsplash.com/640x480/?culture',
            'https://source.unsplash.com/640x480/?innovation',
            'https://source.unsplash.com/640x480/?lifestyle',
            'https://source.unsplash.com/640x480/?city'
        ];

        // Titres et descriptions réalistes
        $titles = [
            'Comment l’IA transforme le monde du travail',
            '10 destinations à visiter absolument en 2025',
            'Les secrets d’une productivité durable',
            'Startup : réussir son premier financement',
            'Les tendances tech qui vont changer notre quotidien',
            'Voyager à petit budget : astuces et bons plans',
            'Bien-être : 5 habitudes pour une meilleure santé mentale',
            'L’éducation en ligne : avenir ou simple mode ?',
            'Culture numérique : comprendre la génération Z',
            'Comment lancer un business en ligne rentable'
        ];

        for ($i = 0; $i < 20; $i++) {
            $title = $faker->randomElement($titles);
            Blog::create([
                'user_id'  => $faker->randomElement($userIds),
                'title'    => $title,
                'content'  => $faker->realTextBetween(800, 1500, 2), // texte long réaliste
                'image'    => $faker->randomElement($images),
                'category' => $faker->randomElement($categories),
                'slug'     => Str::slug($title) . '-' . uniqid(),
            ]);
        }
    }
}
