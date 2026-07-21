<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddEscrowFeeToUserRequests extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('user_requests', 'escrow_fee')) {
                $table->decimal('escrow_fee', 10, 2)->default(0)->after('booking_fee');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            if (Schema::hasColumn('user_requests', 'escrow_fee')) {
                $table->dropColumn('escrow_fee');
            }
        });
    }
}
