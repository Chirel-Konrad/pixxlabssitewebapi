<?php
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public $withinTransaction = false;
    /**
     * Run the migrations.
     */
    public function up(): void
    {
       Schema::create('blogs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // auteur
            $table->string('title');
            $table->text('content');
            $table->string('slug')->unique()->nullable(); // Slug unique pour l'article de blog
            $table->string('image')->nullable(); // Image illustration
            $table->enum('category', [
                'Action',
                'Développement personnel',
                'Technologie',
                'Business',
                'Santé',
                'Lifestyle',
                'Éducation',
                'Divertissement',
                'Culture',
                'Voyage'
            ])->default('Action'); // catégorie par défaut
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blogs');
    }
};

