<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        // Avant de rendre non nullable, on s'assure qu'aucune valeur NULL n'existe
        DB::table('extrausers')->whereNull('niveauEtude')->update(['niveauEtude' => 'inconnu']);
        DB::table('extrausers')->whereNull('domaineEtude')->update(['domaineEtude' => json_encode([])]);
        DB::table('extrausers')->whereNull('disciplineEtude')->update(['disciplineEtude' => json_encode([])]);
        DB::table('extrausers')->whereNull('niveauEtudesouhaite')->update(['niveauEtudesouhaite' => 'inconnu']);
        DB::table('extrausers')->whereNull('document_url')->update(['document_url' => 'default.doc']);
        DB::table('extrausers')->whereNull('photo_url')->update(['photo_url' => 'default.jpg']);

        Schema::table('extrausers', function (Blueprint $table) {
            $table->string('niveauEtude')->nullable(false)->change();
            $table->json('domaineEtude')->nullable(false)->change();
            $table->json('disciplineEtude')->nullable(false)->change();
            $table->string('niveauEtudesouhaite')->nullable(false)->change();
            $table->string('document_url')->nullable(false)->change();
            $table->string('photo_url')->nullable(false)->change();
        });
    }

    public function down(): void
    {
        Schema::table('extrausers', function (Blueprint $table) {
            $table->string('niveauEtude')->nullable()->change();
            $table->json('domaineEtude')->nullable()->change();
            $table->json('disciplineEtude')->nullable()->change();
            $table->string('niveauEtudesouhaite')->nullable()->change();
            $table->string('document_url')->nullable()->change();
            $table->string('photo_url')->nullable()->change();
        });
    }
};
