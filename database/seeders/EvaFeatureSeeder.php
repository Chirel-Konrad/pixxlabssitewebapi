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
                'logo' => 'https://img.icons8.com/fluency/96/faq.png',
            ],
            [
                'title' => 'Orientation personnalisée',
                'description' => 'Guidage intelligent pour explorer efficacement l’univers Piixlabs et découvrir les services adaptés à vos besoins.',
                'logo' => 'https://img.icons8.com/fluency/96/compass.png',
            ],
            [
                'title' => 'Assistance intelligente',
                'description' => 'Un chat IA disponible 24/7 pour vous accompagner avec des conseils, solutions et recommandations sur mesure.',
                'logo' => 'https://img.icons8.com/fluency/96/chatbot.png',
            ],
            [
                'title' => 'Suivi de vos performances',
                'description' => 'Visualisez vos ventes, paiements et progression dans le programme partenaire grâce à un tableau de bord clair.',
                'logo' => 'https://img.icons8.com/fluency/96/combo-chart.png',
            ],
            [
                'title' => 'Boost de réussite',
                'description' => 'Recevez des rappels intelligents et des conseils concrets pour améliorer continuellement vos résultats.',
                'logo' => 'https://img.icons8.com/fluency/96/rocket.png',
            ],
            [
                'title' => 'Suggestions ciblées',
                'description' => 'EVA vous propose les produits et services les plus pertinents selon vos besoins, objectifs et audience.',
                'logo' => 'https://img.icons8.com/fluency/96/target.png',
            ],
        ];

        foreach ($features as $feature) {
            EvaFeature::create($feature + [
                'slug' => Str::slug($feature['title']) . '-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
