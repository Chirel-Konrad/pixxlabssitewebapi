<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Privilege;
use Illuminate\Support\Str;

class PrivilegeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $privileges = [
            [
                'title' => 'Accès à tous les produits Piixlabs à prix réduit exclusif',
                'description' => 'Les partenaires bénéficient de remises exceptionnelles sur tous les produits et services Piixlabs, leur permettant d’accroître leur rentabilité tout en offrant des solutions de qualité à leurs clients.',
                'image' => 'https://images.unsplash.com/photo-1563013544-824ae1b704d3?auto=format&fit=crop&w=800&q=60',
            ],
            [
                'title' => 'Participation aux programmes partenaires (formations, outils, support)',
                'description' => 'Accédez à des formations exclusives, des outils de travail professionnels et un accompagnement dédié pour vous aider à développer votre activité numérique au sein de l’écosystème Piixlabs.',
                'image' => 'https://images.unsplash.com/photo-1605902711622-cfb43c4437d1?auto=format&fit=crop&w=800&q=60',
            ],
            [
                'title' => 'Statut officiel avec avantages et commissions dans notre réseau',
                'description' => 'Devenez un partenaire certifié Piixlabs et profitez d’un statut reconnu, de commissions attractives et d’une visibilité accrue sur nos canaux officiels.',
                'image' => 'https://images.unsplash.com/photo-1554224155-6726b3ff858f?auto=format&fit=crop&w=800&q=60',
            ],
        ];

        foreach ($privileges as $privilege) {
            Privilege::create($privilege + [
                'slug' => Str::slug($privilege['title']) . '-' . uniqid(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
