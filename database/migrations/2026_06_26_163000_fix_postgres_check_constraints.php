<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // 0. Self-heal pdp_stops table to correct PostGIS schema if using outdated schema
            $hasNomArret = DB::select("
                SELECT column_name 
                FROM information_schema.columns 
                WHERE table_name = 'pdp_stops' AND column_name = 'nom_arret'
            ");

            if (empty($hasNomArret)) {
                // Drop old table cascade (drops foreign keys)
                DB::statement("DROP TABLE IF EXISTS pdp_stops CASCADE");

                // Recreate with PostGIS schema
                DB::statement("CREATE TABLE pdp_stops (
                    id bigserial PRIMARY KEY,
                    nom_arret varchar(255) NOT NULL,
                    type_arret varchar(50) DEFAULT 'carrefour',
                    commune_id bigint REFERENCES communes(id) ON DELETE SET NULL,
                    quartier_id bigint REFERENCES quartiers(id) ON DELETE SET NULL,
                    adresse varchar(255) NULL,
                    description text NULL,
                    location geometry(Point, 4326) NULL,
                    latitude decimal(10, 8) NULL,
                    longitude decimal(11, 8) NULL,
                    rayon_validation_metre integer DEFAULT 50,
                    precision_gps varchar(255) NULL,
                    source_coordonnees varchar(50) DEFAULT 'admin',
                    photon_place_id varchar(255) NULL,
                    photon_raw_data json NULL,
                    ors_verified boolean DEFAULT false,
                    statut_validation varchar(50) DEFAULT 'manuel',
                    confidence_score integer DEFAULT 0,
                    created_at timestamp NULL,
                    updated_at timestamp NULL
                )");

                // Recreate GIST spatial index
                DB::statement("CREATE INDEX IF NOT EXISTS pdp_stops_location_gist ON pdp_stops USING GIST (location)");

                // Recreate foreign keys referencing pdp_stops (drop first to ensure idempotency)
                $pdpForeignKeys = [
                    ['pdp_route_segments', 'pdp_route_segments_to_stop_id_foreign', 'to_stop_id', 'SET NULL'],
                    ['pdp_route_segments', 'pdp_route_segments_from_stop_id_foreign', 'from_stop_id', 'SET NULL'],
                    ['package_requests', 'package_requests_pickup_station_id_foreign', 'pickup_station_id', 'SET NULL'],
                    ['package_requests', 'package_requests_dropoff_station_id_foreign', 'dropoff_station_id', 'SET NULL'],
                    ['user_request_passengers', 'user_request_passengers_pickup_pdp_id_foreign', 'pickup_pdp_id', 'SET NULL'],
                    ['user_request_passengers', 'user_request_passengers_dropoff_pdp_id_foreign', 'dropoff_pdp_id', 'SET NULL'],
                    ['user_requests', 'user_requests_grouping_point_id_foreign', 'grouping_point_id', 'SET NULL'],
                    ['active_shared_rides', 'active_shared_rides_next_stop_id_foreign', 'next_stop_id', 'SET NULL'],
                    ['active_shared_rides', 'active_shared_rides_current_stop_id_foreign', 'current_stop_id', 'SET NULL'],
                    ['ride_bookings', 'ride_bookings_start_stop_id_foreign', 'start_stop_id', 'SET NULL'],
                    ['ride_bookings', 'ride_bookings_end_stop_id_foreign', 'end_stop_id', 'SET NULL'],
                    ['station_agents', 'station_agents_pdp_stop_id_foreign', 'pdp_stop_id', 'SET NULL'],
                    ['pdp_route_stops', 'pdp_route_stops_pdp_stop_id_foreign', 'pdp_stop_id', 'CASCADE'],
                    ['partners', 'partners_pdp_stop_id_foreign', 'pdp_stop_id', 'SET NULL'],
                ];

                foreach ($pdpForeignKeys as [$tbl, $constraint, $col, $onDelete]) {
                    // Check that the table and column exist before adding FK
                    $tableExists = DB::select("SELECT 1 FROM information_schema.tables WHERE table_schema = 'public' AND table_name = ?", [$tbl]);
                    $colExists   = DB::select("SELECT 1 FROM information_schema.columns WHERE table_name = ? AND column_name = ?", [$tbl, $col]);
                    if (!empty($tableExists) && !empty($colExists)) {
                        DB::statement("ALTER TABLE {$tbl} DROP CONSTRAINT IF EXISTS {$constraint}");
                        DB::statement("ALTER TABLE {$tbl} ADD CONSTRAINT {$constraint} FOREIGN KEY ({$col}) REFERENCES pdp_stops(id) ON DELETE {$onDelete}");
                    }
                }
            }

            // 1. Fix posts table type constraint
            DB::statement("ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_type_check");
            DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_type_check CHECK (type::text = ANY (ARRAY[
                'TRIP'::text, 
                'NEWS'::text, 
                'VIRAL'::text, 
                'POLL'::text, 
                'SOCIAL'::text, 
                'INTENTION'::text, 
                'RENTAL'::text, 
                'SALE'::text, 
                'SOCIAL_PIC'::text, 
                'SOCIAL_VID'::text, 
                'SOCIAL_POST'::text, 
                'ROAD_INFO'::text, 
                'RSS_NEWS'::text
            ]))");

            // 2. Fix marketplace_listings table type constraint
            DB::statement("ALTER TABLE marketplace_listings DROP CONSTRAINT IF EXISTS marketplace_listings_type_check");
            DB::statement("ALTER TABLE marketplace_listings ADD CONSTRAINT marketplace_listings_type_check CHECK (type::text = ANY (ARRAY[
                'RENTAL'::text, 
                'SALE'::text, 
                'VEHICLE'::text, 
                'ARTICLE'::text
            ]))");

            // 3. Fix subscription_plans table target constraint
            DB::statement("ALTER TABLE subscription_plans DROP CONSTRAINT IF EXISTS subscription_plans_target_check");
            DB::statement("ALTER TABLE subscription_plans ADD CONSTRAINT subscription_plans_target_check CHECK (target::text = ANY (ARRAY[
                'provider'::text, 
                'user'::text, 
                'fleet'::text, 
                'agent'::text,
                'marketplace'::text
            ]))");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            // Revert posts type constraint to initial
            DB::statement("ALTER TABLE posts DROP CONSTRAINT IF EXISTS posts_type_check");
            DB::statement("ALTER TABLE posts ADD CONSTRAINT posts_type_check CHECK (type::text = ANY (ARRAY[
                'TRIP'::text, 
                'NEWS'::text, 
                'VIRAL'::text, 
                'POLL'::text, 
                'SOCIAL'::text
            ]))");

            // Revert marketplace_listings type constraint to initial
            DB::statement("ALTER TABLE marketplace_listings DROP CONSTRAINT IF EXISTS marketplace_listings_type_check");
            DB::statement("ALTER TABLE marketplace_listings ADD CONSTRAINT marketplace_listings_type_check CHECK (type::text = ANY (ARRAY[
                'RENTAL'::text, 
                'SALE'::text
            ]))");

            // Revert subscription_plans target constraint to initial
            DB::statement("ALTER TABLE subscription_plans DROP CONSTRAINT IF EXISTS subscription_plans_target_check");
            DB::statement("ALTER TABLE subscription_plans ADD CONSTRAINT subscription_plans_target_check CHECK (target::text = ANY (ARRAY[
                'provider'::text, 
                'user'::text
            ]))");
        }
    }
};
