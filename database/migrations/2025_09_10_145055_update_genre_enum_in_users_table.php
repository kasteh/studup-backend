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
        // 1. Renommer l'ancien type
        DB::statement("ALTER TYPE genre RENAME TO genre_old");

        // 2. Créer le nouveau type avec les nouvelles valeurs
        DB::statement("CREATE TYPE genre AS ENUM ('homme','femme','ne_se_prononce_pas','personne_trans','non_binaire')");

        // 3. Convertir la colonne pour utiliser le nouveau type
        DB::statement("ALTER TABLE users ALTER COLUMN genre TYPE genre USING genre::text::genre");

        // 4. Supprimer l'ancien type
        DB::statement("DROP TYPE genre_old");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Revenir à l'ancien type enum (male, female, other)
        DB::statement("CREATE TYPE genre_old AS ENUM ('male','female','other')");
        DB::statement("ALTER TABLE users ALTER COLUMN genre TYPE genre_old USING genre::text::genre_old");
        DB::statement("DROP TYPE genre");
        DB::statement("ALTER TYPE genre_old RENAME TO genre");
    }
};
