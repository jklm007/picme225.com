<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Supprime le champ is_outstation_hub de pdp_stops car la logique
     * intercommunale doit être au niveau du service_type, pas de l'arrêt.
     */
    public function up(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            if (Schema::hasColumn('pdp_stops', 'is_outstation_hub')) {
                $table->dropColumn('is_outstation_hub');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->boolean('is_outstation_hub')
                  ->default(false)
                  ->after('commune')
                  ->comment('DEPRECATED: Utiliser service_types.is_intercommunal à la place');
        });
    }
};
