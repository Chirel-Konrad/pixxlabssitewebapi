<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\EvaFeature;
use Illuminate\Support\Str;

class EvaFeatureSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $features = [
            [
                'title' => 'Réponses instantanées',
                'description' => 'Une FAQ dynamique et interactive pour obtenir rapidement des réponses claires à vos questions fréquentes.',
                'logo' => null,
            ],
            [
                'title' => 'Orientation personnalisée',
                'description' => 'Guidage intelligent pour explorer efficacement l’univers Piixlabs et découvrir les services adaptés à vos besoins.',
                'logo' => null,
            ],
            [
                'title' => 'Assistance intelligente',
                'description' => 'Un chat IA disponible 24/7 pour vous accompagner avec des conseils, solutions et recommandations sur mesure.',
                'logo' => null,
            ],
            [
                'title' => 'Suivi de vos performances',
                'description' => 'Visualisez vos ventes, paiements et progression dans le programme partenaire grâce à un tableau de bord clair.',
                'logo' => null,
            ],
            [
                'title' => 'Boost de réussite',
                'description' => 'Recevez des rappels intelligents et des conseils concrets pour améliorer continuellement vos résultats.',
                'logo' => null,
            ],
            [
                'title' => 'Suggestions ciblées',
                'description' => 'EVA vous propose les produits et services les plus pertinents selon vos besoins, objectifs et audience.',
                'logo' => null,
            ],
        ];

        foreach ($features as $feature) {
            // Construire une requête Unsplash à partir du titre
            $query = strtolower($feature['title']);
            $query = str_replace(['é','è','ê','à','ù','ï','î','ô','ç'], ['e','e','e','a','u','i','i','o','c'], $query);
            $query = preg_replace('/[^a-z0-9\s]/', ' ', $query);
            $query = trim(preg_replace('/\s+/', ',', $query));
            $unsplash = 'https://source.unsplash.com/160x160/?'.($query ? $query.',' : '').'ai,technology,assistant';

            EvaFeature::create([
                'title' => $feature['title'],
                'description' => $feature['description'],
                'logo' => $feature['logo'] ?: $unsplash,
                'slug' => Str::slug($feature['title']) . '-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
