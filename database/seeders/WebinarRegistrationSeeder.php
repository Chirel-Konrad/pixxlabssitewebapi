<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\WebinarRegistration;
use App\Models\User;
use App\Models\Webinar;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class WebinarRegistrationSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $userIds = User::pluck('id')->toArray();      // IDs des utilisateurs existants
        $webinarIds = Webinar::pluck('id')->toArray(); // IDs des webinaires existants

        // Générer 50 inscriptions aléatoires
        for ($i = 0; $i < 50; $i++) {
            $userId = $faker->randomElement($userIds);
            $webinarId = $faker->randomElement($webinarIds);

            // Empêche la duplication : un utilisateur ne peut pas être inscrit deux fois au même webinaire
            $exists = WebinarRegistration::where('user_id', $userId)
                ->where('webinar_id', $webinarId)
                ->exists();

            if (!$exists) {
                WebinarRegistration::create([
                    'user_id' => $userId,
                    'webinar_id' => $webinarId,
                    'slug' => Str::slug($userId . '-' . $webinarId) . '-' . uniqid(),
                ]);
            }
        }
    }
}
