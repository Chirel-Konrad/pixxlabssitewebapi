<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('eva_features', function (Blueprint $table) {
            $table->id();
            $table->string('title');           // Titre de la fonctionnalité
            $table->text('description');       // Description détaillée
            $table->string('slug')->unique()->nullable();  // Slug unique pour la fonctionnalité
            $table->string('logo')->nullable(); // Logo ou icône facultatif
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('eva_features');
    }
};
