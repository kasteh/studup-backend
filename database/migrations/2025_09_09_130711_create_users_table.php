<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
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

    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};
