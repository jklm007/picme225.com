<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class ModifySimulatedCommissionLogsMakeRequestIdNullable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('simulated_commission_logs', function (Blueprint $table) {
            // Drop foreign key first
            $table->dropForeign(['request_id']);
            
            // Make request_id nullable
            $table->unsignedInteger('request_id')->nullable()->change();
            
            // Add ride_booking_id
            $table->unsignedBigInteger('ride_booking_id')->nullable()->after('request_id');
            
            // Re-add foreign key for request_id
            $table->foreign('request_id')->references('id')->on('user_requests')->onDelete('cascade');
            
            // Add foreign key for ride_booking_id
            $table->foreign('ride_booking_id')->references('id')->on('ride_bookings')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('simulated_commission_logs', function (Blueprint $table) {
            $table->dropForeign(['ride_booking_id']);
            $table->dropForeign(['request_id']);
            
            $table->dropColumn('ride_booking_id');
            
            // Make request_id non-nullable again
            $table->unsignedInteger('request_id')->change();
            $table->foreign('request_id')->references('id')->on('user_requests')->onDelete('cascade');
        });
    }
}
