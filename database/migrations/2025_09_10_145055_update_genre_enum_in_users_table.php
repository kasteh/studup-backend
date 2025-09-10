<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up(): void
    {
        DB::transaction(function () {
            DB::table('users')->where('genre', 'male')->update(['genre' => 'homme']);
            DB::table('users')->where('genre', 'female')->update(['genre' => 'femme']);
            DB::table('users')->where('genre', 'other')->update(['genre' => 'ne_se_prononce_pas']);

            $constraintName = 'users_genre_check';

            DB::statement("ALTER TABLE users DROP CONSTRAINT {$constraintName}");

            $newValues = "'homme', 'femme', 'ne_se_prononce_pas', 'personne_trans', 'non_binaire'";
            DB::statement("ALTER TABLE users ADD CONSTRAINT {$constraintName} CHECK (genre IN ({$newValues}))");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        DB::transaction(function () {
            DB::table('users')->whereIn('genre', ['personne_trans', 'non_binaire'])->update(['genre' => 'ne_se_prononce_pas']);

            // 1. Reconvertir les valeurs à l'ancien format
            DB::table('users')->where('genre', 'homme')->update(['genre' => 'male']);
            DB::table('users')->where('genre', 'femme')->update(['genre' => 'female']);
            DB::table('users')->where('genre', 'ne_se_prononce_pas')->update(['genre' => 'other']);

            $constraintName = 'users_genre_check';

            // 2. Supprimer la contrainte CHECK actuelle
            DB::statement("ALTER TABLE users DROP CONSTRAINT {$constraintName}");

            // 3. Rajouter l'ancienne contrainte CHECK
            $oldValues = "'male', 'female', 'other'";
            DB::statement("ALTER TABLE users ADD CONSTRAINT {$constraintName} CHECK (genre IN ({$oldValues}))");
        });
    }
};
