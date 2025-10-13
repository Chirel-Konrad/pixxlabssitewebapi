<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        
        // Création de 10 utilisateurs aléatoires
        User::factory()->count(10)->create([
            'phone' => fake()->phoneNumber(),
            'is_2fa_enable' => fake()->boolean(),
            'provider' => null,
            'provider_id' => null,
            'status' => 'active', // ou 'inactive', 'banned' si tu veux varier
        ]);



        // Appelle les autres seeders
        $this->call([
            FaqSeeder::class,
            ProductSeeder::class,
            WebinarSeeder::class,
            BlogSeeder::class,
            TestimonialSeeder::class,
            EvaFeatureSeeder::class,
            PilierSeeder::class,
            PrivilegeSeeder::class,
            OfferSeeder::class,
            BlogCommentSeeder::class,
            WebinarRegistrationSeeder::class,
            ContactSeeder::class,
            NewsletterSeeder::class,
            
        ]);
    }
}

