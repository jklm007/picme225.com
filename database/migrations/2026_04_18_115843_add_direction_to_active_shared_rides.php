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
        Schema::table('active_shared_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('active_shared_rides', 'destination_latitude')) {
                $table->decimal('destination_latitude', 10, 8)->nullable()->after('current_longitude');
            }
            if (!Schema::hasColumn('active_shared_rides', 'destination_longitude')) {
                $table->decimal('destination_longitude', 11, 8)->nullable()->after('destination_latitude');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('active_shared_rides', function (Blueprint $table) {
            $table->dropColumn(['destination_latitude', 'destination_longitude']);
        });
    }
};
