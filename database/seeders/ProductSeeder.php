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
        Product::create([
            'name' => 'Kodeow',
            'description' => 'Votre plateforme de services digitaux à la demande, pensée pour vous donner un accès simple et rapide aux compétences numériques qui font avancer vos projets.',
            'slug' => Str::slug('Kodeow') . '-' . uniqid(),
            'price' => 0.00,
            'image' => 'https://source.unsplash.com/640x480/?technology,digital',
            'status' => 'available'
        ]);

        Product::create([
            'name' => 'Wurabook',
            'description' => 'Apprenez à votre rythme avec des cours pratiques, pensés pour l’Afrique, qui transforment vos ambitions en compétences réelles : Skills School, Growth School & Business School.',
            'slug' => Str::slug('Wurabook') . '-' . uniqid(),
            'price' => 0.00,
            'image' => 'https://source.unsplash.com/640x480/?education,learning',
            'status' => 'available'
        ]);

        Product::create([
            'name' => 'Adgriow',
            'description' => 'La plateforme qui connecte facilement marques et influenceurs africains. Grâce à un matching intelligent et des outils simples, boostez vos campagnes marketing avec efficacité et transparence.',
            'slug' => Str::slug('Adgriow') . '-' . uniqid(),
            'price' => 0.00,
            'image' => 'https://source.unsplash.com/640x480/?marketing,network',
            'status' => 'available'
        ]);

        Product::create([
            'name' => 'Proadvysor',
            'description' => 'Votre accès rapide à des conseils personnalisés par des pros en business, carrière, finance, développement personnel et bien plus encore, pour avancer plus vite dans tous vos projets.',
            'slug' => Str::slug('Proadvysor') . '-' . uniqid(),
            'price' => 0.00,
            'image' => 'https://source.unsplash.com/640x480/?business,consulting',
            'status' => 'available'
        ]);

        Product::create([
            'name' => 'Bloowee',
            'description' => 'Votre accès direct à des opportunités de networking, d’échanges et de collaborations, pour développer votre carrière, élargir votre réseau et créer des connexions qui comptent.',
            'slug' => Str::slug('Bloowee') . '-' . uniqid(),
            'price' => 0.00,
            'image' => 'https://source.unsplash.com/640x480/?networking,people',
            'status' => 'available'
        ]);

        Product::create([
            'name' => 'Trivascan',
            'description' => 'La plateforme qui détecte les arnaques, analyse les signaux faibles et fournit des insights fiables pour sécuriser vos transactions et décisions business. Avancez avec confiance.',
            'slug' => Str::slug('Trivascan') . '-' . uniqid(),
            'price' => 0.00,
            'image' => 'https://source.unsplash.com/640x480/?security,data',
            'status' => 'available'
        ]);
    }
}
