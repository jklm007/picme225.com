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
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->double('insurance_fee', 10, 2)->default(0)->after('provider_pay');
            $table->double('syndicate_fee', 10, 2)->default(0)->after('insurance_fee');
            $table->double('cooperative_fee', 10, 2)->default(0)->after('syndicate_fee');
            $table->double('dao_treasury_fee', 10, 2)->default(0)->after('cooperative_fee');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('user_request_payments', function (Blueprint $table) {
            $table->dropColumn(['insurance_fee', 'syndicate_fee', 'cooperative_fee', 'dao_treasury_fee']);
        });
    }
};
