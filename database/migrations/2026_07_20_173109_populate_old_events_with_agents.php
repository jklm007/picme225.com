<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use App\Models\MarketplaceListing;
use App\Models\MarketplaceAgent;
use App\Models\Partner;
use App\Models\StationAgent;

class PopulateOldEventsWithAgents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Script to populate `marketplace_agents` for existing events
        // Assign all existing STATION_AGENT partners to all existing TICKETS listings
        $listings = DB::table('marketplace_listings')->where('type', 'TICKETS')->orWhere('type', 'TRAVEL')->get();
        $agents = DB::table('partners')->where('type', 'STATION_AGENT')->get();
        
        $inserts = [];
        foreach ($listings as $listing) {
            foreach ($agents as $agent) {
                if ($agent->user_id) {
                    $inserts[] = [
                        'listing_id' => $listing->id,
                        'user_id' => $agent->user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Also check StationAgent model
        $stationAgents = DB::table('station_agents')->get();
        foreach ($listings as $listing) {
            foreach ($stationAgents as $agent) {
                if ($agent->user_id) {
                    $inserts[] = [
                        'listing_id' => $listing->id,
                        'user_id' => $agent->user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ];
                }
            }
        }
        
        // Insert unique combinations
        $inserts = array_map("unserialize", array_unique(array_map("serialize", $inserts)));
        
        // Ignore duplicates during insert
        foreach ($inserts as $data) {
            DB::table('marketplace_agents')->updateOrInsert([
                'listing_id' => $data['listing_id'],
                'user_id' => $data['user_id']
            ], $data);
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        // No down needed
    }
}
;
