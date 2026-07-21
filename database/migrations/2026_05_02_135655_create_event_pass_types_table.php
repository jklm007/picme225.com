<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Table pour les types de passes (les configurations d'horaires)
        Schema::create('event_pass_types', function (Blueprint $table) {
            $table->id();
            $table->integer('post_id')->nullable(); // Lié au Post Social pour la diffusion
            $table->integer('event_id')->nullable(); // Lié à un événement (Voyage ou Concert)
            
            $table->string('name'); // ex: "Pass Brunch", "Pass Soirée"
            $table->decimal('price', 10, 2)->default(0);
            
            $table->time('valid_from'); // Heure de début (ex: 12:00:00)
            $table->time('valid_until'); // Heure de fin (ex: 06:00:00)
            
            $table->integer('quantity')->default(0);
            $table->integer('sold_count')->default(0);
            
            $table->timestamps();
            $table->softDeletes();
        });

        // 2. Ajout de colonnes de support aux billets existants si nécessaire
        // On va plutôt créer une table de billets unifiée ou adapter les existantes via une autre migration si besoin.
        // Pour l'instant, on se concentre sur la définition des passes.
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('event_pass_types');
    }
};
