<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('webinars', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('video_url');
            $table->string('slug')->unique()->nullable();

            // ✅ Nouveaux champs demandés
            $table->string('whose');    // Pour qui est destiné le webinaire
            $table->string('date');     // Date du webinaire (ex: Mardi)
            $table->string('time');     // Heure du webinaire (ex: 18H GMT)
            $table->string('image');    // Image du webinaire (ex: chemin/URL)

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('webinars');
    }
};
