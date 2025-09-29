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
                'description' => 'Une FAQ dynamique pour obtenir rapidement des réponses à vos questions fréquentes.',
                'logo' => 'reponses_instantanees.png',
            ],
            [
                'title' => 'Orientation personnalisée',
                'description' => 'Guidage intelligent pour explorer l’univers Piixlabs et découvrir les services adaptés à vos besoins.',
                'logo' => 'orientation_personnalisee.png',
            ],
            [
                'title' => 'Assistance intelligente',
                'description' => 'Un chat IA disponible pour vous accompagner avec des conseils et des solutions personnalisées.',
                'logo' => 'assistance_intelligente.png',
            ],
            [
                'title' => 'Suivi de vos performances',
                'description' => 'Accédez facilement à vos ventes, paiements et progression dans le programme partenaire.',
                'logo' => 'suivi_performances.png',
            ],
            [
                'title' => 'Boost de réussite',
                'description' => 'Recevez des rappels et des recommandations concrètes pour améliorer vos résultats.',
                'logo' => 'boost_reussite.png',
            ],
            [
                'title' => 'Suggestions ciblées',
                'description' => 'EVA vous propos les produits et services les plus pertinents selon vos objectifs et votre audience.',
                'logo' => 'suggestions_ciblees.png',
            ],
        ];

        foreach ($features as $feature) {
            EvaFeature::create($feature + [
                'slug' => Str::slug($feature['title']) . '-' . uniqid(),
            ]);
        }
    }
}
