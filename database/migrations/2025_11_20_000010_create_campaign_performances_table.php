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
        Schema::create('campaign_performances', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_campaign_id');
            $table->unsignedBigInteger('ad_platform_id')->nullable();
            $table->date('date');
            $table->integer('impressions')->default(0);
            $table->integer('clicks')->default(0);
            $table->integer('conversions')->default(0);
            $table->decimal('spent', 15, 2)->default(0);
            $table->decimal('ctr', 5, 2)->default(0)->comment('Click-Through Rate en %');
            $table->decimal('cpc', 10, 2)->default(0)->comment('Cost Per Click');
            $table->decimal('cpm', 10, 2)->default(0)->comment('Cost Per Mille');
            $table->decimal('conversion_rate', 5, 2)->default(0)->comment('Taux de conversion en %');
            $table->json('additional_metrics')->nullable()->comment('Métriques supplémentaires par plateforme');
            $table->timestamps();

            $table->foreign('ad_campaign_id')->references('id')->on('ad_campaigns')->onDelete('cascade');
            $table->foreign('ad_platform_id')->references('id')->on('ad_platforms')->onDelete('set null');
            $table->index(['ad_campaign_id', 'date']);
            $table->unique(['ad_campaign_id', 'ad_platform_id', 'date'], 'unique_campaign_performance');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('campaign_performances');
    }
};

