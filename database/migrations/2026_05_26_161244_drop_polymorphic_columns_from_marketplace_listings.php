<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropColumn([
                'brand', 'model', 'year', 'color', 'plate_number', 'with_driver',
                'location_city', 'location_latitude', 'location_longitude', 'price_unit',
                'stock_quantity', 'home_delivery', 'delivery_price', 'is_digital', 'digital_file_path', 'pdp_route_id'
            ]);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('plate_number')->nullable();
            $table->boolean('with_driver')->nullable();

            $table->string('location_city')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->string('price_unit')->nullable();

            $table->integer('stock_quantity')->nullable();
            $table->boolean('home_delivery')->nullable();
            $table->decimal('delivery_price', 10, 2)->nullable();
            $table->boolean('is_digital')->nullable();
            $table->string('digital_file_path')->nullable();

            $table->unsignedBigInteger('pdp_route_id')->nullable();
        });
    }
};
