<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('fleets', function (Blueprint $table) {
            $table->integer('unpaid_months_count')->default(0)->after('financial_mode');
            $table->boolean('is_restricted')->default(false)->after('unpaid_months_count'); // Manual block if needed
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('fleets', function (Blueprint $table) {
            $table->dropColumn(['unpaid_months_count', 'is_restricted']);
        });
    }
};
