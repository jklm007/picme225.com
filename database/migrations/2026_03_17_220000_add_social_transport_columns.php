<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Ajoute les colonnes nécessaires aux tables existantes pour l'intégration sociale.
     * Compatible avec l'architecture existante (posts, user_requests, ride_bookings).
     */
    public function up(): void
    {
        // 1. Augmenter la table posts pour supporter toutes les fonctionnalités sociales
        Schema::table('posts', function (Blueprint $table) {
            if (!Schema::hasColumn('posts', 'pdp_route_id')) {
                $table->unsignedInteger('pdp_route_id')->nullable()->comment('Corridor de Route (ex: Abidjan-Bassam)');
            }
            if (!Schema::hasColumn('posts', 'service_type_id')) {
                $table->unsignedInteger('service_type_id')->nullable();
            }
            if (!Schema::hasColumn('posts', 'is_shareable')) {
                $table->boolean('is_shareable')->default(false);
            }
            if (!Schema::hasColumn('posts', 'seats_available')) {
                $table->unsignedTinyInteger('seats_available')->default(0);
            }
            if (!Schema::hasColumn('posts', 'pledge_count')) {
                $table->unsignedInteger('pledge_count')->default(0);
            }
            if (!Schema::hasColumn('posts', 'pledge_threshold')) {
                $table->unsignedInteger('pledge_threshold')->default(4);
            }
            if (!Schema::hasColumn('posts', 'status')) {
                $table->enum('status', ['ACTIVE', 'CLOSED', 'CANCELLED', 'PLEDGING'])->default('ACTIVE');
            }
            if (!Schema::hasColumn('posts', 'expires_at')) {
                $table->timestamp('expires_at')->nullable();
            }
        });

        // 2. Créer la table des pledges (engagements communautaires)
        Schema::create('post_pledges', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('post_id');
            $table->unsignedInteger('user_id');
            $table->decimal('pickup_latitude', 10, 8)->nullable();
            $table->decimal('pickup_longitude', 11, 8)->nullable();
            $table->string('pickup_address')->nullable();
            $table->enum('status', ['PLEDGED', 'CONFIRMED', 'CANCELLED'])->default('PLEDGED');
            $table->timestamps();

            $table->unique(['post_id', 'user_id']);
            $table->index('post_id');
        });

        // 3. Ajouter les colonnes sociales aux user_requests (pour le Share & Save)
        Schema::table('user_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('user_requests', 'is_shareable')) {
                $table->boolean('is_shareable')->default(false)->comment('La course accepte-t-elle des passagers supplémentaires ?');
            }
            if (!Schema::hasColumn('user_requests', 'linked_post_id')) {
                $table->unsignedInteger('linked_post_id')->nullable()->comment('Post social lié à cette course');
            }
        });

        // 4. Ajouter la commission à ride_bookings
        Schema::table('ride_bookings', function (Blueprint $table) {
            if (!Schema::hasColumn('ride_bookings', 'commission_amount')) {
                $table->decimal('commission_amount', 10, 2)->default(0)->comment('Commission Picme (15%)');
            }
            if (!Schema::hasColumn('ride_bookings', 'driver_amount')) {
                $table->decimal('driver_amount', 10, 2)->default(0)->comment('Part chauffeur (85%)');
            }
            if (!Schema::hasColumn('ride_bookings', 'escrow_status')) {
                $table->enum('escrow_status', ['HELD', 'RELEASED', 'REFUNDED'])->default('HELD');
            }
        });

        // 5. Ajouter le flag is_flagged aux post_comments (anti-contournement)
        Schema::table('post_comments', function (Blueprint $table) {
            if (!Schema::hasColumn('post_comments', 'is_flagged')) {
                $table->boolean('is_flagged')->default(false);
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('post_pledges');

        Schema::table('posts', function (Blueprint $table) {
            $table->dropColumn([
                'pdp_route_id', 'service_type_id', 'is_shareable',
                'seats_available', 'pledge_count', 'pledge_threshold', 'status', 'expires_at',
            ]);
        });

        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn(['is_shareable', 'linked_post_id']);
        });

        Schema::table('ride_bookings', function (Blueprint $table) {
            $table->dropColumn(['commission_amount', 'driver_amount', 'escrow_status']);
        });
    }
};
