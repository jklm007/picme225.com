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
    $table->enum('sharing_type', ['NONE', 'DYNAMIC_POOL', 'PDP'])->default('NONE')->after('status');
});



    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (Schema::hasColumn('service_types', 'sharing_type')) {
                $table->dropColumn('sharing_type');
            }
        });
    }
};
