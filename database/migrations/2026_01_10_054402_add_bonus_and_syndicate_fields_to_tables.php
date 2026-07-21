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
        Schema::table('providers', function (Blueprint $table) {
            $table->boolean('is_syndicated')->default(false)->after('status');
        });

        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->double('bonus_fee', 10, 2)->default(0)->after('dao_treasury_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn('is_syndicated');
        });

        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn('bonus_fee');
        });
    }
};
