<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\BlogComment;
use App\Models\Blog;
use App\Models\User;
use Faker\Factory as Faker;

class BlogCommentSeeder extends Seeder
{
    public function run(): void
    {
        $faker = Faker::create();

        $blogIds = Blog::pluck('id')->toArray();
        $userIds = User::pluck('id')->toArray();

        // Exemples de phrases de départ pour donner plus de naturel
        $commentStarts = [
            "Super article !",
            "Merci pour ce partage,",
            "Je ne suis pas tout à fait d’accord,",
            "Très intéressant,",
            "Waouh,",
            "Bonne analyse,",
            "J’ai une question :",
            "Merci beaucoup,",
            "Excellent contenu,",
            "Article inspirant,"
        ];

        // Expressions de conclusion
        $commentEnds = [
            "cela m’a vraiment fait réfléchir.",
            "je vais tester vos conseils.",
            "ça me motive à passer à l’action.",
            "continuez comme ça !",
            "je recommande la lecture.",
            "j’aimerais en savoir plus.",
            "pouvez-vous développer ce point ?",
            "je partage totalement votre avis.",
            "ça m’aide pour mon projet.",
            "merci encore pour ces infos."
        ];

        // Générer 100 commentaires réalistes
        for ($i = 0; $i < 100; $i++) {
            $start = $faker->randomElement($commentStarts);
            $middle = $faker->realTextBetween(40, 120, 2); // du texte naturel
            $end = $faker->randomElement($commentEnds);

            BlogComment::create([
                'blog_id' => $faker->randomElement($blogIds),
                'user_id' => $faker->optional()->randomElement($userIds), // certains anonymes
                'comment' => trim("$start $middle $end"),
            ]);
        }
    }
}
