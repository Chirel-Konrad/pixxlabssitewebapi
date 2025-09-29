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
                'description' => 'Nous rendons l’apprentissage accessible, pratique et pertinent pour tous ceux qui souhaitent évoluer dans le digital, lancer un projet ou renforcer leurs compétences.',
                'image' => 'apprendre.png'
            ],
            [
                'title' => 'Se Connecter',
                'description' => 'Nous aidons chacun à se connecter aux bonnes personnes, aux bons outils et aux bonnes opportunités. Un réseau actif de talents, de créateurs, de freelances, d’entrepreneurs et de partenaires à travers l’Afrique.',
                'image' => 'se_connecter.png'
            ],
            [
                'title' => 'Réussir',
                'description' => 'Nous croyons que la réussite doit être inclusive, durable et méritée. Nos outils permettent à chacun de gagner de l’argent, valoriser son savoir-faire, collaborer et évoluer professionnellement.',
                'image' => 'reussir.png'
            ],
        ];

        foreach ($piliers as $pilier) {
            Pilier::create($pilier + [
                'slug' => Str::slug($pilier['title']) . '-' . uniqid(),
            ]);
        }
    }
}
