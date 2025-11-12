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
       Schema::create('faqs', function (Blueprint $table) {
    $table->id();
    $table->enum('type', ['home', 'webinars', 'partner', 'AI']); // <--- enum ici
    $table->string('question');
    $table->string('slug')->unique()->nullable();
    $table->string('description')->nullable(); // facultatif, explication du type
    $table->timestamps();
});
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('faqs');
    }
};


