<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('extrausers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('cascade');
            $table->string('niveauEtude')->nullable();
            $table->json('domaineEtude')->nullable();
            $table->json('disciplineEtude')->nullable();
            $table->string('niveauEtudesouhaite')->nullable();
            $table->string('document_url')->nullable();
            $table->string('photo_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('extrausers');
    }
};
