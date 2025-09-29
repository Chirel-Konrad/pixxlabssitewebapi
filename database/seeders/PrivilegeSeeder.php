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
                'description' => '',
                'image' => 'produits_reduits.png',
            ],
            [
                'title' => 'Participation aux programmes partenaires (formations, outils, support)',
                'description' => '',
                'image' => 'programmes_partenaires.png',
            ],
            [
                'title' => 'Statut officiel avec avantages et commissions dans notre réseau',
                'description' => '',
                'image' => 'statut_officiel.png',
            ],
        ];

        foreach ($privileges as $privilege) {
            Privilege::create($privilege + [
                'slug' => Str::slug($privilege['title']) . '-' . uniqid(),
            ]);
        }
    }
}
