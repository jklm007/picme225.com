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
            if (!Schema::hasColumn('service_types', 'is_communal')) {
                $table->boolean('is_communal')->default(false)->after('status');
            }
            if (!Schema::hasColumn('service_types', 'max_distance')) {
                $table->decimal('max_distance', 10, 2)->default(15.00)->after('is_communal');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn(['is_communal', 'max_distance']);
        });
    }
};
