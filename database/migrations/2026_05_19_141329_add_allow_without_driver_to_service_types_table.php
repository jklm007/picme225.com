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
            if (!Schema::hasColumn('service_types', 'allow_without_driver')) {
                $table->boolean('allow_without_driver')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('service_types', function (Blueprint $table) {
            if (Schema::hasColumn('service_types', 'allow_without_driver')) {
                $table->dropColumn('allow_without_driver');
            }
        });
    }
};
