<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubscriptionToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->unsignedBigInteger('subscription_plan_id')->nullable()->after('fleet_id');
            $table->timestamp('subscription_expires_at')->nullable()->after('subscription_plan_id');
            
            $table->foreign('subscription_plan_id')->references('id')->on('subscription_plans')->onDelete('set null');
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->boolean('requires_pro_subscription')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['subscription_plan_id']);
            $table->dropColumn(['subscription_plan_id', 'subscription_expires_at']);
        });

        Schema::table('service_types', function (Blueprint $table) {
            $table->dropColumn('requires_pro_subscription');
        });
    }
}
