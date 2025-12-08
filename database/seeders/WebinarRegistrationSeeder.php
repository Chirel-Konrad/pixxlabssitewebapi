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

        // Pour chaque utilisateur, créer 1 à 3 inscriptions aléatoires (sans doublon)
        foreach ($userIds as $uid) {
            $count = $faker->numberBetween(1, min(3, count($webinarIds)));
            $picked = $faker->randomElements($webinarIds, $count);

            foreach ($picked as $wid) {
                $exists = WebinarRegistration::where('user_id', $uid)
                    ->where('webinar_id', $wid)
                    ->exists();

                if (!$exists) {
                    WebinarRegistration::create([
                        'user_id' => (int) $uid,
                        'webinar_id' => (int) $wid,
                        'slug' => Str::slug($uid . '-' . $wid) . '-' . uniqid(),
                    ]);
                }
            }
        }
    }
}
