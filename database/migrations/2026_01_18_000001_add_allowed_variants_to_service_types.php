<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            // JSON column to store allowed ride variants for each service type
            $table->json('allowed_variants')->nullable()->after('capacity');

            // Discount percentage for 'arret' variant (if applicable)
            $table->decimal('arret_discount_percent', 5, 2)->nullable()->after('allowed_variants')
                ->comment('Réduction en % pour la variante Arrêt (ex: 20.00 pour 20%)');
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['allowed_variants', 'arret_discount_percent']);
        });
    }
};
