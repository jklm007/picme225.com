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
        Schema::table('active_shared_rides', function (Blueprint $table) {
            if (!Schema::hasColumn('active_shared_rides', 'service_type_id')) {
                $table->unsignedBigInteger('service_type_id')->nullable()->after('provider_id');
                $table->foreign('service_type_id')->references('id')->on('service_types')->onDelete('set null');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('active_shared_rides', function (Blueprint $table) {
            if (Schema::hasColumn('active_shared_rides', 'service_type_id')) {
                $table->dropForeign(['service_type_id']);
                $table->dropColumn('service_type_id');
            }
        });
    }
};

