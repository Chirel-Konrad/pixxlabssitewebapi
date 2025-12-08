<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Product;
use Illuminate\Support\Str;

class ProductSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $products = [
            [
                'name' => 'Kodeow',
                'description' => 'Votre plateforme de services digitaux à la demande, pensée pour vous donner un accès simple et rapide aux compétences numériques qui font avancer vos projets.',
                'image' => 'https://source.unsplash.com/640x480/?technology,digital',
            ],
            [
                'name' => 'Wurabook',
                'description' => 'Apprenez à votre rythme avec des cours pratiques, pensés pour l’Afrique, qui transforment vos ambitions en compétences réelles : Skills School, Growth School & Business School.',
                'image' => 'https://source.unsplash.com/640x480/?education,learning',
            ],
            [
                'name' => 'Adgriow',
                'description' => 'La plateforme qui connecte facilement marques et influenceurs africains. Grâce à un matching intelligent et des outils simples, boostez vos campagnes marketing avec efficacité et transparence.',
                'image' => 'https://source.unsplash.com/640x480/?marketing,network',
            ],
            [
                'name' => 'Proadvysor',
                'description' => 'Votre accès rapide à des conseils personnalisés par des pros en business, carrière, finance, développement personnel et bien plus encore, pour avancer plus vite dans tous vos projets.',
                'image' => 'https://source.unsplash.com/640x480/?business,consulting',
            ],
            [
                'name' => 'Bloowee',
                'description' => 'Votre accès direct à des opportunités de networking, d’échanges et de collaborations, pour développer votre carrière, élargir votre réseau et créer des connexions qui comptent.',
                'image' => 'https://source.unsplash.com/640x480/?networking,people',
            ],
            [
                'name' => 'Trivascan',
                'description' => 'La plateforme qui détecte les arnaques, analyse les signaux faibles et fournit des insights fiables pour sécuriser vos transactions et décisions business. Avancez avec confiance.',
                'image' => 'https://source.unsplash.com/640x480/?security,data',
            ],
        ];

        foreach ($products as $p) {
            $price = random_int(0, 19999) / 100; // 0.00 à 199.99
            Product::create([
                'name' => $p['name'],
                'description' => $p['description'],
                'slug' => Str::slug($p['name']) . '-' . uniqid(),
                'price' => $price,
                'image' => $p['image'], // URL externe conservée telle quelle
                'status' => 'available'
            ]);
        }
    }
}
