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
        Schema::table('pdp_routes', function (Blueprint $table) {
            $table->string('service_class')->default('NORMAL')->after('type')->comment('VIP, NORMAL, etc.');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('pdp_routes', function (Blueprint $table) {
            $table->dropColumn('service_class');
        });
    }
};
