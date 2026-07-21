<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePackageRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('package_requests', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->string('sender_name')->nullable();
            $table->string('sender_phone')->nullable();
            $table->string('recipient_name');
            $table->string('recipient_phone');

            // Pickup details (could be station or address)
            $table->unsignedBigInteger('pickup_station_id')->nullable();
            $table->string('s_address')->nullable();
            $table->double('s_latitude', 15, 8)->nullable();
            $table->double('s_longitude', 15, 8)->nullable();

            // Dropoff details (could be station or address)
            $table->unsignedBigInteger('dropoff_station_id')->nullable();
            $table->string('d_address')->nullable();
            $table->double('d_latitude', 15, 8)->nullable();
            $table->double('d_longitude', 15, 8)->nullable();

            // Logistics Type
            $table->enum('type', ['INSTANT_DELIVERY', 'STATION_FREIGHT'])->default('INSTANT_DELIVERY');
            $table->unsignedBigInteger('interurban_company_id')->nullable();

            // Package Info
            $table->string('description')->nullable();
            $table->string('package_type')->nullable(); // Box, Document, etc.
            $table->string('size_category')->nullable(); // S, M, L, XL
            $table->double('weight', 8, 2)->default(0);
            $table->string('picture')->nullable();

            // Status & Tracking
            $table->enum('status', ['CREATED', 'PENDING_PICKUP', 'DEPOSITED', 'IN_TRANSIT', 'ARRIVED', 'DELIVERED', 'CANCELLED'])->default('CREATED');
            $table->string('tracking_code')->unique();
            $table->string('otp_pickup')->nullable();

            // Pricing
            $table->double('distance', 10, 2)->default(0);
            $table->double('price', 15, 2)->default(0);
            $table->string('payment_mode')->default('CASH');
            $table->boolean('paid')->default(false);

            // Assignments
            $table->unsignedBigInteger('provider_id')->nullable(); // For Instant Delivery

            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('pickup_station_id')->references('id')->on('pdp_stops')->onDelete('set null');
            $table->foreign('dropoff_station_id')->references('id')->on('pdp_stops')->onDelete('set null');
            $table->foreign('interurban_company_id')->references('id')->on('interurban_companies')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('package_requests');
    }
}
