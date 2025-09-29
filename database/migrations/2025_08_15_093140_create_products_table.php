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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Nom du produit
            $table->text('description')->nullable(); // Description du produit
            $table->string('slug')->unique()->nullable(); // Slug unique pour le produit
            $table->decimal('price', 10, 2); // Prix du produit
            $table->string('image')->nullable(); // Image du produit

            // ✅ Nouveau champ status
            $table->enum('status', ['available', 'pending'])->default('pending');
            // available = disponible | pending = en attente

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
