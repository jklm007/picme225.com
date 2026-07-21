<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (!Schema::hasColumn('service_types', 'price_per_segment')) {
                $table->decimal('price_per_segment', 10, 2)->default(0);
            }
            if (!Schema::hasColumn('service_types', 'km_per_segment')) {
                $table->decimal('km_per_segment', 8, 2)->nullable()
                    ->after('price_per_segment')
                    ->comment('Distance en KM correspondant à 1 segment PDP (ex: 2.5)');
            }
        });
    }

    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (Schema::hasColumn('service_types', 'km_per_segment')) {
                $table->dropColumn('km_per_segment');
            }
            if (Schema::hasColumn('service_types', 'price_per_segment')) {
                $table->dropColumn('price_per_segment');
            }
        });
    }
};
