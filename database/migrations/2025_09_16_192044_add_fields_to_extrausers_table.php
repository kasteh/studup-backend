<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('extrausers', function (Blueprint $table) {
            $table->json('pays');
            $table->string('period');
            $table->json('langue');
            $table->string('niveauLangue');
        });
    }

    public function down(): void
    {
        Schema::table('extrausers', function (Blueprint $table) {
            $table->dropColumn(['pays', 'period', 'langue', 'niveauLangue']);
        });
    }
};
