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
        Schema::table('user_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('user_requests', 'with_driver')) {
                $table->boolean('with_driver')->default(true);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            if (Schema::hasColumn('user_requests', 'with_driver')) {
                $table->dropColumn('with_driver');
            }
        });
    }
};
