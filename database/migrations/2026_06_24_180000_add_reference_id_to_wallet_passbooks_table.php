<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     * Adds reference_id to wallet_passbooks (links a passbook entry to
     * an external entity such as a trip, referral user, parcel, etc.)
     */
    public function up(): void
    {
        Schema::table('wallet_passbooks', function (Blueprint $table) {
            if (!Schema::hasColumn('wallet_passbooks', 'reference_id')) {
                $table->string('reference_id')->nullable()->after('description');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('wallet_passbooks', function (Blueprint $table) {
            if (Schema::hasColumn('wallet_passbooks', 'reference_id')) {
                $table->dropColumn('reference_id');
            }
        });
    }
};
