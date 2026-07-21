<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

class AddStandardVariantsAndDelivery extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // 1. Variantes et Delivery pour user_requests
        Schema::table('user_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('user_requests', 'ride_variant')) {
                $table->enum('ride_variant', ['prive', 'dynamique', 'arret'])->default('prive')->after('service_type_id');
            }
            if (!Schema::hasColumn('user_requests', 'delivery_meta')) {
                $table->json('delivery_meta')->nullable()->comment('Détails livraison: sender, description, instructions')->after('ride_variant');
            }
            if (!Schema::hasColumn('user_requests', 'stops_data')) {
                $table->json('stops_data')->nullable()->comment('Liste des arrêts pour livraison ou dynamique')->after('delivery_meta');
            }
            if (!Schema::hasColumn('user_requests', 'pickup_stop_id')) {
                $table->unsignedBigInteger('pickup_stop_id')->nullable()->after('stops_data');
                // $table->foreign('pickup_stop_id')->references('id')->on('pdp_stops'); // On évite la contrainte stricte pour l'instant si nullable
            }
            if (!Schema::hasColumn('user_requests', 'dropoff_stop_id')) {
                $table->unsignedBigInteger('dropoff_stop_id')->nullable()->after('pickup_stop_id');
            }
            if (!Schema::hasColumn('user_requests', 'detour_distance')) {
                $table->decimal('detour_distance', 10, 2)->default(0)->comment('Distance ajoutée par le détour')->after('dropoff_stop_id');
            }
        });

        // 2. Gestion des arrêts (DAO / Public)
        Schema::table('pdp_stops', function (Blueprint $table) {
            if (!Schema::hasColumn('pdp_stops', 'status')) {
                $table->enum('status', ['PENDING', 'APPROVED', 'REJECTED'])->default('PENDING')->after('is_active');
            }
            if (!Schema::hasColumn('pdp_stops', 'is_public')) {
                $table->boolean('is_public')->default(false)->comment('Visible par tous si true')->after('status');
            }
            if (!Schema::hasColumn('pdp_stops', 'creator_id')) {
                $table->unsignedBigInteger('creator_id')->nullable()->after('is_public');
            }
            if (!Schema::hasColumn('pdp_stops', 'creator_type')) {
                $table->string('creator_type')->nullable()->comment('USER ou PROVIDER')->after('creator_id');
            }
            if (!Schema::hasColumn('pdp_stops', 'votes_count')) {
                $table->integer('votes_count')->default(0)->after('creator_type');
            }
        });

        // 3. Modifier l'enum DAO Proposal Type
        // Note: Modifier un enum existant peut être complexe selon la DB. On tente une approche raw SQL sûre.
        try {
            if (DB::connection()->getDriverName() === 'mysql') {
                DB::statement("ALTER TABLE dao_proposals MODIFY COLUMN type ENUM('PRICE_CHANGE', 'ROUTE_ADDITION', 'ROUTE_MODIFICATION', 'PARAMETER_CHANGE', 'STOP_ADDITION') NOT NULL");
            }
        } catch (\Exception $e) {
            // Fallback si la colonne n'existe pas ou erreur
            // On suppose que la table existe car vue précédemment
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('user_requests', function (Blueprint $table) {
            $table->dropColumn(['ride_variant', 'delivery_meta', 'stops_data', 'pickup_stop_id', 'dropoff_stop_id', 'detour_distance']);
        });

        Schema::table('pdp_stops', function (Blueprint $table) {
            $table->dropColumn(['status', 'is_public', 'creator_id', 'creator_type', 'votes_count']);
        });
        
        // On ne revert pas l'enum DAO pour éviter perte de données si rollback partiel
    }
}
