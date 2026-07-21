<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBonusTrackingToProvidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->integer('total_rides_lifetime')->default(0)->after('eco_wallet_balance');
            $table->integer('consecutive_days_active')->default(0)->after('total_rides_lifetime');
            $table->date('last_active_date')->nullable()->after('consecutive_days_active');
            $table->integer('cancellations_last_7_days')->default(0)->after('last_active_date');
            $table->integer('cancellations_last_30_days')->default(0)->after('cancellations_last_7_days');
            $table->decimal('total_bonus_earned', 10, 4)->default(0)->after('cancellations_last_30_days')->comment('Total bonus en ECO');
            $table->string('current_tier')->default('BRONZE')->after('total_bonus_earned')->comment('BRONZE, SILVER, GOLD, PLATINUM, DIAMOND');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('providers', function (Blueprint $table) {
            $table->dropColumn([
                'total_rides_lifetime',
                'consecutive_days_active',
                'last_active_date',
                'cancellations_last_7_days',
                'cancellations_last_30_days',
                'total_bonus_earned',
                'current_tier'
            ]);
        });
    }
}
