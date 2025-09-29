<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Testimonial;
use App\Models\User;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class TestimonialSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Récupère TOUS les IDs utilisateurs existants
        $userIds = User::pluck('id')->toArray();

        for ($i = 0; $i < 20; $i++) {
            $content = $faker->paragraphs(3, true);
            Testimonial::create([
                // ✅ user_id jamais null
                'user_id' => $faker->randomElement($userIds),
                'content' => $content,
                'slug' => Str::slug(substr($content, 0, 50)) . '-' . uniqid(),
            ]);
        }
    }
}
