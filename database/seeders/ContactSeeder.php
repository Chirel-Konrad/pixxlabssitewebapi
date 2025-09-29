<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Contact;
use Faker\Factory as Faker;
use Illuminate\Support\Str;

class ContactSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        // Quelques contacts de démonstration
        for ($i = 0; $i < 20; $i++) {
            $firstname = $faker->firstName();
            $lastname  = $faker->lastName();
            $email     = $faker->unique()->safeEmail();
            $message   = $faker->paragraphs(2, true);

            Contact::create([
                'firstname' => $firstname,
                'lastname'  => $lastname,
                'email'     => $email,
                'message'   => $message,
                'slug'      => Str::slug($firstname . ' ' . $lastname) . '-' . uniqid(),
            ]);
        }
    }
}
