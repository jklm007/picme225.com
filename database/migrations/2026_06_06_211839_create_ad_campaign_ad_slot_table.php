<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ad_campaign_ad_slot', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_campaign_id');
            $table->unsignedBigInteger('ad_slot_id');
            $table->timestamps();

            $table->foreign('ad_campaign_id')->references('id')->on('ad_campaigns')->onDelete('cascade');
            $table->foreign('ad_slot_id')->references('id')->on('ad_slots')->onDelete('cascade');
            
            $table->unique(['ad_campaign_id', 'ad_slot_id']);
        });

        // Migrate existing data
        DB::table('ad_campaigns')->whereNotNull('ad_slot_id')->orderBy('id')->chunk(100, function ($campaigns) {
            $inserts = [];
            foreach ($campaigns as $campaign) {
                $inserts[] = [
                    'ad_campaign_id' => $campaign->id,
                    'ad_slot_id' => $campaign->ad_slot_id,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
            DB::table('ad_campaign_ad_slot')->insert($inserts);
        });

        // Now drop the ad_slot_id column from ad_campaigns
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropForeign(['ad_slot_id']);
            $table->dropColumn('ad_slot_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('ad_slot_id')->nullable();
            $table->foreign('ad_slot_id')->references('id')->on('ad_slots')->onDelete('set null');
        });

        // Revert data
        DB::table('ad_campaign_ad_slot')->orderBy('id')->chunk(100, function ($pivots) {
            foreach ($pivots as $pivot) {
                DB::table('ad_campaigns')
                    ->where('id', $pivot->ad_campaign_id)
                    ->update(['ad_slot_id' => $pivot->ad_slot_id]);
            }
        });

        Schema::dropIfExists('ad_campaign_ad_slot');
    }
};
