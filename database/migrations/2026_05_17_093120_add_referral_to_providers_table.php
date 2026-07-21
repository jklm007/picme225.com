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
        Schema::table('providers', function (Blueprint $table) {
            if (!Schema::hasColumn('providers', 'referral_unique_id')) {
                $table->string('referral_unique_id', 20)->nullable()->unique();
            }
            if (!Schema::hasColumn('providers', 'referred_by_id')) {
                $table->unsignedInteger('referred_by_id')->nullable();
            }
            if (!Schema::hasColumn('providers', 'referral_count')) {
                $table->integer('referral_count')->default(0);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['referral_unique_id', 'referred_by_id', 'referral_count']);
        });
    }
};
