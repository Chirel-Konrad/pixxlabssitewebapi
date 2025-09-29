<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Offer;
use Illuminate\Support\Str;

class OfferSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $offers = [
            [
                'title' => 'Former les talents africains du numérique',
                'description' => 'Offrir des formations accessibles et pertinentes pour développer les compétences digitales en Afrique.'
            ],
            [
                'title' => 'Outiller les entrepreneurs et indépendants',
                'description' => 'Mettre à disposition des outils et ressources pour faciliter le lancement et la gestion de projets.'
            ],
            [
                'title' => 'Distribuer des solutions utiles et abordables',
                'description' => 'Rendre accessibles des produits et services numériques de qualité à un prix abordable.'
            ],
            [
                'title' => 'Favoriser les connexions et collaborations locales et panafricaines',
                'description' => 'Créer un réseau actif de talents, entrepreneurs et partenaires à travers l’Afrique.'
            ],
            [
                'title' => 'Protéger les utilisateurs contre les arnaques et pratiques douteuses',
                'description' => 'Mettre en place des mesures de sécurité et des outils de contrôle pour protéger les utilisateurs.'
            ],
        ];

        foreach ($offers as $offer) {
            $title = $offer['title'];
            Offer::create($offer + [
                'slug' => Str::slug($title) . '-' . uniqid(),
            ]);
        }
    }
}
