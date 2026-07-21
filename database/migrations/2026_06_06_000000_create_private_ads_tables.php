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
        // 1. Create advertisers table
        Schema::create('advertisers', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('company_name')->nullable();
            $table->string('email')->unique();
            $table->string('phone')->nullable();
            $table->enum('status', ['ACTIVE', 'INACTIVE'])->default('ACTIVE');
            $table->timestamps();
        });

        // 2. Create ad_slots table
        Schema::create('ad_slots', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique() ->comment('e.g., HOME_BANNER, TRIP_COMPLETED');
            $table->text('description')->nullable();
            $table->string('admob_unit_id')->nullable() ->comment('Fallback AdMob Unit ID');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // 3. Add advertiser_id, ad_slot_id, limits to ad_campaigns
        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->unsignedBigInteger('advertiser_id')->nullable()->after('user_id');
            $table->unsignedBigInteger('ad_slot_id')->nullable()->after('advertiser_id');
            $table->integer('max_impressions')->default(0)->comment('0 = unlimited');
            $table->integer('max_clicks')->default(0)->comment('0 = unlimited');
            $table->integer('current_impressions')->default(0);
            $table->integer('current_clicks')->default(0);

            $table->foreign('advertiser_id')->references('id')->on('advertisers')->onDelete('set null');
            $table->foreign('ad_slot_id')->references('id')->on('ad_slots')->onDelete('set null');
        });

        // 4. Create ad_impressions table
        Schema::create('ad_impressions', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_campaign_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ad_campaign_id')->references('id')->on('ad_campaigns')->onDelete('cascade');
        });

        // 5. Create ad_clicks table
        Schema::create('ad_clicks', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_campaign_id');
            $table->unsignedBigInteger('user_id')->nullable();
            $table->string('ip_address', 45)->nullable();
            $table->timestamp('created_at')->useCurrent();

            $table->foreign('ad_campaign_id')->references('id')->on('ad_campaigns')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_clicks');
        Schema::dropIfExists('ad_impressions');

        Schema::table('ad_campaigns', function (Blueprint $table) {
            $table->dropForeign(['advertiser_id']);
            $table->dropForeign(['ad_slot_id']);
            $table->dropColumn(['advertiser_id', 'ad_slot_id', 'max_impressions', 'max_clicks', 'current_impressions', 'current_clicks']);
        });

        Schema::dropIfExists('ad_slots');
        Schema::dropIfExists('advertisers');
    }
};
