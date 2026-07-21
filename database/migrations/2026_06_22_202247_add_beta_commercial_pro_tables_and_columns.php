<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddBetaCommercialProTablesAndColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Alter users table
        Schema::table('users', function (Blueprint $table) {
            $table->enum('member_tier', ['FREE', 'STANDARD', 'PREMIUM', 'PRO'])->default('FREE')->after('is_vip');
            $table->timestamp('pro_unlocked_at')->nullable()->after('member_tier');
        });

        // 2. Create store_receipts table
        Schema::create('store_receipts', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedBigInteger('user_id');
            $table->string('product_id');
            $table->enum('store_type', ['google_play', 'apple_appstore']);
            $table->text('purchase_token');
            $table->string('transaction_id')->unique();
            $table->timestamp('purchase_date')->useCurrent();
            $table->enum('status', ['VALIDATED', 'EXPIRED', 'REFUNDED'])->default('VALIDATED');
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });

        // 3. Create simulated_commission_logs table
        Schema::create('simulated_commission_logs', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('request_id');
            $table->decimal('total_amount', 10, 2);
            $table->decimal('simulated_commission', 10, 2);
            $table->decimal('simulated_provider_pay', 10, 2);
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('request_id')->references('id')->on('user_requests')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // Drop tables
        Schema::dropIfExists('simulated_commission_logs');
        Schema::dropIfExists('store_receipts');

        // Drop columns from users
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['member_tier', 'pro_unlocked_at']);
        });
    }
}
