<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $enumTypeName = 'enum_name';

        $newValues = [
            'homme',
            'femme',
            'ne_se_prononce_pas',
            'personne_trans',
            'non_binaire'
        ];

        foreach ($newValues as $value) {
            DB::statement("
                DO $$ BEGIN
                    BEGIN
                        ALTER TYPE {$enumTypeName} ADD VALUE IF NOT EXISTS '{$value}';
                    EXCEPTION
                        WHEN duplicate_object THEN NULL;
                    END;
                END $$;
            ");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // PostgreSQL ne permet pas de supprimer directement des valeurs enum
        // Pour rollback, il faudrait recréer le type depuis zéro
    }
};
