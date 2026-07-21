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
            $table->boolean('use_karma')->default(false)->after('use_wallet');
            $table->integer('karma_points_used')->default(0)->after('use_karma');
        });

        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->float('karma_discount', 10, 2)->default(0)->after('discount');
            $table->float('karma_redeem', 10, 2)->default(0)->after('karma_discount');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn(['use_karma', 'karma_points_used']);
        });

        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn(['karma_discount', 'karma_redeem']);
        });
    }
};
