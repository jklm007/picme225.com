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
        Schema::table('service_service_type', function (Blueprint $table) {
            if (!Schema::hasColumn('service_service_type', 'is_taxable')) {
                $table->boolean('is_taxable')->default(true);
            }
            if (!Schema::hasColumn('service_service_type', 'is_communal')) {
                $table->boolean('is_communal')->default(false);
            }
            if (!Schema::hasColumn('service_service_type', 'max_distance')) {
                $table->decimal('max_distance', 10, 2)->default(15.00);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_service_type', function (Blueprint $table) {
            $table->dropColumn(['is_taxable', 'is_communal', 'max_distance']);
        });
    }
};
