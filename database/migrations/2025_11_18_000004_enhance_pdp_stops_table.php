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
        Schema::table('pdp_stops', function (Blueprint $table) {
            if (!Schema::hasColumn('pdp_stops', 'description')) {
                $table->text('description')->nullable()->after('is_active');
            }

            if (!Schema::hasColumn('pdp_stops', 'max_waiting_time')) {
                $table->integer('max_waiting_time')->nullable()->after('description');
            }

            if (!Schema::hasColumn('pdp_stops', 'allowed_service_types')) {
                $table->json('allowed_service_types')->nullable()->after('max_waiting_time');
            }

            if (!Schema::hasColumn('pdp_stops', 'priority')) {
                $table->integer('priority')->default(0)->after('allowed_service_types');
            }

            if (!Schema::hasColumn('pdp_stops', 'is_recommended')) {
                $table->boolean('is_recommended')->default(false)->after('priority');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_stops', function (Blueprint $table) {
            $columns = [
                'description',
                'max_waiting_time',
                'allowed_service_types',
                'priority',
                'is_recommended',
            ];

            foreach ($columns as $column) {
                if (Schema::hasColumn('pdp_stops', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};

