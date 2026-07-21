<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            // CHANGE: Using $table->id() is the modern and recommended way
            // It creates a BIGINT UNSIGNED AUTO_INCREMENT column,
            // which is compatible with foreignId() and ensures consistency.
            $table->id(); // WAS: $table->increments('id');

            $table->string('first_name');
            $table->string('last_name');
            $table->enum('payment_mode', ['CASH', 'CARD', 'PAYPAL']);
            $table->string('email')->unique();
            $table->string('mobile')->unique();
            $table->string('password');
            $table->string('picture')->nullable();
            $table->string('device_token')->nullable();
            $table->string('device_id')->nullable();
            $table->enum('device_type',array('android','ios'));
            $table->enum('login_by',array('manual','facebook','google'));
            $table->string('social_unique_id')->nullable();
            $table->double('latitude', 15, 8)->nullable();
            $table->double('longitude',15,8)->nullable();
            $table->string('stripe_cust_id')->nullable();
            $table->float('wallet_balance')->default(0);
            $table->decimal('rating', 4, 2)->default(5);
            $table->mediumInteger('otp')->default(0);
            $table->rememberToken();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
}
