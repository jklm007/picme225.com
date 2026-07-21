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
        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'wallet_address')) {
                $table->string('wallet_address')->nullable()->unique()->after('wallet_balance');
            }
            if (!Schema::hasColumn('users', 'eco_token_balance')) {
                $table->decimal('eco_token_balance', 20, 8)->default(0)->after('wallet_address');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            if (Schema::hasColumn('users', 'wallet_address')) {
                $table->dropUnique(['wallet_address']);
                $table->dropColumn('wallet_address');
            }
            if (Schema::hasColumn('users', 'eco_token_balance')) {
                $table->dropColumn('eco_token_balance');
            }
        });
    }
};

