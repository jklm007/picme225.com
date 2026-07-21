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
        Schema::create('ad_contents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ad_campaign_id');
            $table->enum('content_type', ['TEXT', 'IMAGE', 'VIDEO', 'AUDIO', 'CAROUSEL'])->default('TEXT');
            $table->string('title')->nullable();
            $table->text('headline')->nullable();
            $table->text('description')->nullable();
            $table->text('call_to_action')->nullable();
            $table->string('image_url')->nullable();
            $table->string('video_url')->nullable();
            $table->string('audio_url')->nullable();
            $table->json('keywords')->nullable()->comment('Mots-clés pour la campagne');
            $table->json('platform_specific_data')->nullable()->comment('Données spécifiques par plateforme');
            $table->boolean('is_ai_generated')->default(false);
            $table->text('ai_prompt')->nullable()->comment('Prompt utilisé pour générer le contenu');
            $table->timestamps();

            $table->foreign('ad_campaign_id')->references('id')->on('ad_campaigns')->onDelete('cascade');
            $table->index('ad_campaign_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_contents');
    }
};

