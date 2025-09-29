<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Newsletter;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class NewsletterSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Générer des emails de test
        for ($i = 0; $i < 20; $i++) {
            $email = $faker->unique()->safeEmail();
            $local = explode('@', $email)[0];

            Newsletter::create([
                'email' => $email,
                'slug'  => Str::slug($local) . '-' . uniqid(),
            ]);
        }
    }
}
