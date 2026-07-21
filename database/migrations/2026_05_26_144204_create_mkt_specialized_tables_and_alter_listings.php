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
        // 1. Ajouter les colonnes polymorphes à la table principale
        Schema::table('marketplace_listings', function (Blueprint $table) {
            if (!Schema::hasColumn('marketplace_listings', 'listable_type')) {
                $table->string('listable_type')->nullable()->after('id');
            }
            if (!Schema::hasColumn('marketplace_listings', 'listable_id')) {
                $table->unsignedBigInteger('listable_id')->nullable()->after('listable_type');
            }
            $table->index(['listable_type', 'listable_id']);
        });

        // 2. Création des 6 tables spécialisées
        Schema::create('mkt_real_estates', function (Blueprint $table) {
            $table->id();
            $table->string('location_city')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->string('price_unit')->nullable(); // day, month
            $table->timestamps();
        });

        Schema::create('mkt_vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('year')->nullable();
            $table->string('color')->nullable();
            $table->string('plate_number')->nullable();
            $table->boolean('with_driver')->default(false);
            $table->decimal('driver_price', 10, 2)->nullable();
            $table->string('driving_policy')->nullable();
            $table->timestamps();
        });

        Schema::create('mkt_logistics', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('pdp_route_id')->nullable();
            $table->timestamps();
        });

        Schema::create('mkt_events', function (Blueprint $table) {
            $table->id();
            $table->dateTime('start_date')->nullable();
            $table->dateTime('end_date')->nullable();
            $table->timestamps();
        });

        Schema::create('mkt_services', function (Blueprint $table) {
            $table->id();
            $table->string('price_unit')->nullable();
            $table->timestamps();
        });

        Schema::create('mkt_products', function (Blueprint $table) {
            $table->id();
            $table->integer('stock_quantity')->default(1);
            $table->boolean('home_delivery')->default(false);
            $table->decimal('delivery_price', 10, 2)->nullable();
            $table->boolean('is_digital')->default(false);
            $table->string('digital_file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('mkt_products');
        Schema::dropIfExists('mkt_services');
        Schema::dropIfExists('mkt_events');
        Schema::dropIfExists('mkt_logistics');
        Schema::dropIfExists('mkt_vehicles');
        Schema::dropIfExists('mkt_real_estates');

        Schema::table('marketplace_listings', function (Blueprint $table) {
            $table->dropIndex(['listable_type', 'listable_id']);
            $table->dropColumn(['listable_type', 'listable_id']);
        });
    }
};
