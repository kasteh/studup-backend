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
        // Vérifier si la table existe déjà pour éviter doublons
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table) {
                $table->id();
                $table->enum('userType', ['student', 'organism', 'admin']);
                $table->string('prenom');
                $table->string('nom');
                $table->string('nationalite');
                $table->date('birthDate');
                $table->enum('genre', ['male', 'female', 'other']);
                $table->string('adresse');
                $table->string('codePostale');
                $table->string('pays');
                $table->string('numeroTelephone');
                $table->string('emailUniversitaire')->unique();
                $table->string('motdepasse');
                $table->boolean('terms');
                $table->timestamps();
            });
        }

        // Table pour les tokens de réinitialisation de mot de passe
        if (!Schema::hasTable('password_reset_tokens')) {
            Schema::create('password_reset_tokens', function (Blueprint $table) {
                $table->string('email')->primary();
                $table->string('token');
                $table->timestamp('created_at')->nullable();
            });
        }

        // Table pour les sessions (facultatif, si tu gères manuellement)
        if (!Schema::hasTable('sessions')) {
            Schema::create('sessions', function (Blueprint $table) {
                $table->string('id')->primary();
                $table->foreignId('user_id')->nullable()->index();
                $table->string('ip_address', 45)->nullable();
                $table->text('user_agent')->nullable();
                $table->longText('payload');
                $table->integer('last_activity')->index();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('users');
    }
};
