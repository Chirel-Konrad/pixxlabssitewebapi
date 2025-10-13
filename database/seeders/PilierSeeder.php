<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Pilier;
use Illuminate\Support\Str;

class PilierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $piliers = [
            [
                'title' => 'Apprendre',
                'description' => 'Nous rendons l’apprentissage accessible, pratique et pertinent pour tous ceux qui souhaitent évoluer dans le digital, lancer un projet ou renforcer leurs compétences. À travers nos programmes, chacun peut progresser à son rythme et transformer sa passion en expertise.',
                'image' => 'https://images.unsplash.com/photo-1503676260728-1c00da094a0b?auto=format&fit=crop&w=800&q=60',
            ],
            [
                'title' => 'Se Connecter',
                'description' => 'Nous aidons chacun à se connecter aux bonnes personnes, aux bons outils et aux bonnes opportunités. Notre communauté réunit des talents, des créateurs, des freelances et des entrepreneurs pour favoriser la collaboration et l’entraide à travers l’Afrique.',
                'image' => 'https://images.unsplash.com/photo-1521737604893-d14cc237f11d?auto=format&fit=crop&w=800&q=60',
            ],
            [
                'title' => 'Réussir',
                'description' => 'Nous croyons que la réussite doit être inclusive, durable et méritée. Nos outils permettent à chacun de gagner de l’argent, valoriser son savoir-faire, collaborer efficacement et construire un avenir professionnel solide.',
                'image' => 'https://images.unsplash.com/photo-1506784983877-45594efa4cbe?auto=format&fit=crop&w=800&q=60',
            ],
            [
                'title' => 'Innover',
                'description' => 'Nous encourageons la créativité et l’innovation à travers des projets ambitieux et des solutions numériques adaptées aux réalités africaines. Notre mission : bâtir ensemble un futur digital fort et authentique.',
                'image' => 'https://images.unsplash.com/photo-1522199710521-72d69614c702?auto=format&fit=crop&w=800&q=60',
            ],
            [
                'title' => 'Impacter',
                'description' => 'Au-delà du succès individuel, nous visons un impact collectif. Chaque action menée par la communauté Piixlabs contribue à un écosystème plus juste, plus connecté et plus prospère pour le continent.',
                'image' => 'https://images.unsplash.com/photo-1485217988980-11786ced9454?auto=format&fit=crop&w=800&q=60',
            ],
        ];

        foreach ($piliers as $pilier) {
            Pilier::create($pilier + [
                'slug' => Str::slug($pilier['title']) . '-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
