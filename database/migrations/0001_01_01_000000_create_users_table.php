<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    // ✅ CRITIQUE : Désactiver les transactions pour PostgreSQL
    public $withinTransaction = false;
    
    public function up(): void
    {
        // Supprimer les types s'ils existent déjà (au cas où)
        DB::statement("DROP TYPE IF EXISTS user_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS user_role CASCADE");
        
        // Créer les types ENUM PostgreSQL
        DB::statement("CREATE TYPE user_status AS ENUM ('active', 'inactive', 'banned')");
        DB::statement("CREATE TYPE user_role AS ENUM ('user', 'admin', 'superadmin')");
        
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('phone')->nullable();
            $table->boolean('is_2fa_enable')->default(false);
            $table->string('provider')->nullable();
            $table->rememberToken();
            $table->string('provider_id')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();
        });
        
        // Ajouter les colonnes ENUM
        DB::statement("ALTER TABLE users ADD COLUMN status user_status DEFAULT 'active'");
        DB::statement("ALTER TABLE users ADD COLUMN role user_role DEFAULT 'user'");

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email', 255)->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
        
        DB::statement("DROP TYPE IF EXISTS user_status CASCADE");
        DB::statement("DROP TYPE IF EXISTS user_role CASCADE");
    }
};