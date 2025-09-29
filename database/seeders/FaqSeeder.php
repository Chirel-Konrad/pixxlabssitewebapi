<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Faq;
use App\Models\FaqAnswer;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class FaqSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();
        $types = ['home', 'webinars', 'partner', 'AI'];

        // FAQ statiques
        $faqData = [
            [
                'type' => 'home',
                'question' => 'C’est quoi Piixlabs, en vrai ?',
                'description' => 'FAQ destinée à la section accueil',
                'answers' => [
                    'Le portail pour apprendre, se connecter et réussir grâce au digital en Afrique. Une seule plateforme. Des dizaines de solutions. Que vous soyez débutant ou pro, Piixlabs vous aide à lancer vos idées, apprendre de nouvelles compétences, créer vos projets et même générer des revenus. Tout est là. En un seul endroit.'
                ]
            ],
            [
                'type' => 'home',
                'question' => 'Un seul compte pour tout faire ?',
                'description' => 'FAQ destinée à la section accueil',
                'answers' => [
                    'Oui un seul compte peut tout faire sur Piixlabs. Inscrivez-vous gratuitement avec votre email ou via Google/Facebook. Une fois connecté, vous aurez accès à toutes les sections : Apprendre, Se connecter et Réussir.'
                ]
            ],
            [
    'type' => 'webinars',
    'question' => 'Pourquoi assister à un webinaire ?',
    'description' => 'FAQ destinée à la section webinars',
    'answers' => [
        'Pour bien comprendre ce qu’est Piixlabs, comment ça fonctionne, et ce que vous pouvez en tirer.',
        'Pour découvrir comment devenir partenaire affilié et générer des revenus.',
        'Pour avoir des réponses en live, sans filtre.'
    ]
],

            [
                'type' => 'webinars',
                'question' => 'Comment participer aux webinars ?',
                'description' => 'FAQ destinée à la section webinars',
                'answers' => [
                    'Inscrivez-vous gratuitement sur notre plateforme, puis rendez-vous dans la section Webinars pour choisir ceux qui vous intéressent.',
                    'Vous recevrez un lien de connexion par email avant chaque session.'
                ]
            ],
            [
                'type' => 'partner',
                'question' => 'Comment devenir partenaire Piixlabs ?',
                'description' => 'FAQ destinée à la section partenaires',
                'answers' => [
                    'Pour devenir partenaire, inscrivez-vous sur notre site et accédez à la section Partenaires.',
                    'Remplissez le formulaire de candidature et notre équipe vous contactera rapidement.'
                ]
            ],
            [
                'type' => 'AI',
                'question' => 'Qui est EVA ?',
                'description' => 'FAQ destinée à la section IA',
                'answers' => [
                    'EVA est l’assistante virtuelle de Piixlabs, pensée pour vous guider, vous informer et vous aider à tirer le meilleur de la plateforme.'
                ]
            ],
        ];

        // Création des FAQs statiques
        foreach ($faqData as $data) {
            $faq = Faq::create([
                'type' => $data['type'],
                'question' => $data['question'],
                'description' => $data['description'],
                'slug' => Str::slug($data['question']) . '-' . uniqid(),
            ]);

            foreach ($data['answers'] as $ans) {
                $faq->answers()->create(['answer' => $ans]);
            }
        }

        // Générer des FAQs fictives
        for ($i = 0; $i < 15; $i++) {
            $question = $faker->sentence(6, true);
            $faq = Faq::create([
                'type' => $faker->randomElement($types),
                'question' => $question,
                'description' => $faker->sentence(5, true),
                'slug' => Str::slug($question) . '-' . uniqid(),
            ]);

            // Ajouter 1 à 3 réponses fictives par FAQ
            $numAnswers = rand(1, 3);
            for ($j = 0; $j < $numAnswers; $j++) {
                $faq->answers()->create([
                    'answer' => $faker->paragraph(2, true)
                ]);
            }
        }
    }
}
