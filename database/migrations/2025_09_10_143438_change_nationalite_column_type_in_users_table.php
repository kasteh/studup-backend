<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Convertir string existante en JSONB
        // Exemple : 'France' → ["France"]
        DB::statement("
            ALTER TABLE users 
            ALTER COLUMN nationalite 
            TYPE jsonb 
            USING to_jsonb(array[nationalite]);
        ");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à string (on prend le premier élément du tableau)
        DB::statement("
            ALTER TABLE users 
            ALTER COLUMN nationalite 
            TYPE varchar 
            USING (nationalite->>0);
        ");
    }
};
