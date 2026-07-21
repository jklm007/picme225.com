<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBookingFeeSetting extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Ajouter la configuration des frais de réservation (150 CFA par défaut)
        DB::table('settings')->updateOrInsert(
            ['key' => 'platform_booking_fee'],
            ['value' => '150']
        );
        
        // Ajouter la colonne booking_fee à la table user_requests si elle n'existe pas
        if (!Schema::hasColumn('user_requests', 'booking_fee')) {
            Schema::table('user_requests', function (Blueprint $table) {
                $table->decimal('booking_fee', 10, 2)->default(0); 
            });
        }
        
        // Ajouter aussi dans user_request_payments
        if (!Schema::hasColumn('user_request_payments', 'booking_fee')) {
            Schema::table('user_request_payments', function (Blueprint $table) {
                $table->decimal('booking_fee', 10, 2)->default(0);
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::table('settings')->where('key', 'platform_booking_fee')->delete();
        
        if (Schema::hasColumn('user_requests', 'booking_fee')) {
            Schema::table('user_requests', function (Blueprint $table) {
                $table->dropColumn('booking_fee');
            });
        }
        
        if (Schema::hasColumn('user_request_payments', 'booking_fee')) {
            Schema::table('user_request_payments', function (Blueprint $table) {
                $table->dropColumn('booking_fee');
            });
        }
    }
}
