<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // 1. Table des annonces Marketplace (Vente & Location longue durée)
        Schema::create('marketplace_listings', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id');
            $table->enum('type', ['RENTAL', 'SALE'])->comment('RENTAL=Location, SALE=Vente');
            $table->string('title');
            $table->text('description');
            $table->decimal('price', 12, 2)->comment('Prix de vente ou tarif journalier/heure');
            $table->enum('price_unit', ['HOUR', 'DAY', 'WEEK', 'MONTH', 'FIXED'])->default('DAY');
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->year('year')->nullable();
            $table->string('color')->nullable();
            $table->string('plate_number')->nullable();
            $table->string('cover_image')->nullable();
            $table->json('images')->nullable();
            $table->boolean('with_driver')->default(false);
            $table->decimal('deposit_amount', 10, 2)->default(0)->comment('Caution de sécurité');
            $table->decimal('delivery_price', 10, 2)->default(0)->comment('Frais de dépôt à domicile');
            $table->boolean('home_delivery')->default(false);
            $table->string('location_city')->nullable();
            $table->decimal('location_latitude', 10, 8)->nullable();
            $table->decimal('location_longitude', 11, 8)->nullable();
            $table->unsignedInteger('pdp_route_id')->nullable()->comment('Corridor de route associé');
            $table->enum('status', ['ACTIVE', 'RESERVED', 'SOLD', 'PAUSED'])->default('ACTIVE');
            $table->timestamps();
            $table->softDeletes();

            $table->index(['type', 'status']);
            $table->index('user_id');
        });

        // 2. Table des réservations de location (avec caution Escrow)
        Schema::create('rental_bookings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('listing_id');
            $table->unsignedInteger('user_id');
            $table->timestamp('start_at');
            $table->timestamp('end_at')->nullable();
            $table->decimal('total_price', 10, 2);
            $table->decimal('deposit_amount', 10, 2)->default(0);
            $table->decimal('delivery_price', 10, 2)->default(0);
            $table->decimal('commission_amount', 10, 2)->default(0)->comment('Commission Picme 15%');
            $table->decimal('owner_amount', 10, 2)->default(0)->comment('Part propriétaire 85%');
            $table->enum('pickup_type', ['SELF', 'HOME_DELIVERY'])->default('SELF');
            $table->string('delivery_address')->nullable();
            $table->decimal('delivery_latitude', 10, 8)->nullable();
            $table->decimal('delivery_longitude', 11, 8)->nullable();
            $table->enum('status', ['PENDING', 'CONFIRMED', 'ACTIVE', 'COMPLETED', 'CANCELLED'])->default('PENDING');
            $table->enum('escrow_status', ['HELD', 'RELEASED', 'REFUNDED'])->default('HELD');
            $table->enum('deposit_status', ['HELD', 'RELEASED', 'DEDUCTED'])->default('HELD');
            $table->string('vehicle_condition_start')->nullable()->comment('URL photo état du véhicule au départ');
            $table->string('vehicle_condition_end')->nullable()->comment('URL photo état du véhicule au retour');
            $table->string('qr_code')->nullable()->comment('QR Code de remise des clés');
            $table->timestamps();

            $table->foreign('listing_id')->references('id')->on('marketplace_listings');
            $table->index(['listing_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rental_bookings');
        Schema::dropIfExists('marketplace_listings');
    }
};
