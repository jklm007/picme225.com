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
        Schema::create('ad_platforms', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_campaign_id');
            $table->enum('platform', ['GOOGLE_ADS', 'FACEBOOK_ADS', 'TIKTOK_ADS', 'IN_APP', 'IN_VEHICLE'])->default('GOOGLE_ADS');
            $table->string('platform_campaign_id')->nullable()->comment('ID de la campagne sur la plateforme externe');
            $table->enum('status', ['PENDING', 'ACTIVE', 'PAUSED', 'COMPLETED', 'ERROR'])->default('PENDING');
            $table->decimal('spent', 15, 2)->default(0)->comment('Montant dépensé sur cette plateforme');
            $table->json('platform_config')->nullable()->comment('Configuration spécifique à la plateforme');
            $table->text('error_message')->nullable();
            $table->timestamps();

            $table->foreign('ad_campaign_id')->references('id')->on('ad_campaigns')->onDelete('cascade');
            $table->index(['ad_campaign_id', 'platform']);
            $table->unique(['ad_campaign_id', 'platform'], 'unique_campaign_platform');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_platforms');
    }
};

