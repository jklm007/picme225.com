<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateServiceTypesAndProvidersForTokenomics extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Mise à jour de service_types
        Schema::table('service_types', function (Blueprint $table) {
            // Logique Feeder & Intercity
            if (!Schema::hasColumn('service_types', 'is_intercity')) {
                $table->boolean('is_intercity')->default(false)->after('description');
            }
            if (!Schema::hasColumn('service_types', 'requires_feeder_ride')) {
                $table->boolean('requires_feeder_ride')->default(false)->after('is_intercity');
            }
            if (!Schema::hasColumn('service_types', 'can_act_as_feeder')) {
                $table->boolean('can_act_as_feeder')->default(false)->after('requires_feeder_ride');
            }
            
            // Paramètres Feeder
            if (!Schema::hasColumn('service_types', 'feeder_trigger_radius')) {
                $table->integer('feeder_trigger_radius')->default(5)->after('can_act_as_feeder'); // en km
            }
            
            // Tokenomics
            if (!Schema::hasColumn('service_types', 'commission_percentage')) {
                $table->integer('commission_percentage')->default(15)->after('feeder_trigger_radius');
            }
            if (!Schema::hasColumn('service_types', 'eco_discount_percent')) {
                $table->integer('eco_discount_percent')->default(0)->after('commission_percentage');
            }
        });

        // 2. Mise à jour de providers
        Schema::table('providers', function (Blueprint $table) {
            $table->decimal('eco_wallet_balance', 15, 4)->default(0.0000)->after('status');
            $table->date('bonus_expires_at')->nullable()->after('eco_wallet_balance');
        });

        // 3. Mise à jour de users (VIP & Overdraft)
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_vip')->default(false)->after('wallet_balance');
            $table->decimal('overdraft_limit', 10, 2)->default(0.00)->after('is_vip');
        });

        // 4. Mise à jour de user_requests (Chain & Ticket)
        Schema::table('user_requests', function (Blueprint $table) {
            $table->string('chain_uuid')->nullable()->after('booking_id'); // Pour lier Feeder -> Intercity
            $table->string('ticket_qr_code')->nullable()->after('chain_uuid');
            $table->boolean('is_feeder_ride')->default(false)->after('ticket_qr_code');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn([
                'is_intercity', 
                'requires_feeder_ride', 
                'can_act_as_feeder', 
                'feeder_trigger_radius', 
                'commission_percentage', 
                'eco_discount_percent'
            ]);
        });

        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn(['eco_wallet_balance', 'bonus_expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_vip', 'overdraft_limit']);
        });

        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn(['chain_uuid', 'ticket_qr_code', 'is_feeder_ride']);
        });
    }
}
