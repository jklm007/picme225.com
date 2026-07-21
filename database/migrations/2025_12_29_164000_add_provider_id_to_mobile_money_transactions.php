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
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();
            $table->unsignedBigInteger('provider_id')->nullable()->after('user_id');

            // On élargit l'enum provider
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE mobile_money_transactions MODIFY COLUMN provider ENUM('orange', 'mtn', 'moov', 'cinetpay')");
            }

            $table->foreign('provider_id')->references('id')->on('providers')->onDelete('cascade');
            $table->index(['provider_id', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('mobile_money_transactions', function (Blueprint $table) {
            $table->dropForeign(['provider_id']);
            $table->dropColumn('provider_id');
            // Revert enum if needed, but risky
        });
    }
};
