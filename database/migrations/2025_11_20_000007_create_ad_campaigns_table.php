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
        Schema::create('ad_campaigns', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id')->comment('Client qui crée la campagne');
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('status', ['DRAFT', 'ACTIVE', 'PAUSED', 'COMPLETED', 'CANCELLED'])->default('DRAFT');
            $table->enum('campaign_type', ['BRAND_AWARENESS', 'LEAD_GENERATION', 'SALES', 'TRAFFIC', 'ENGAGEMENT'])->default('BRAND_AWARENESS');
            $table->decimal('budget', 15, 2)->comment('Budget total de la campagne');
            $table->decimal('daily_budget', 15, 2)->nullable()->comment('Budget quotidien');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->json('target_audience')->nullable()->comment('Cible: âge, sexe, localisation, intérêts');
            $table->json('ai_generated_content')->nullable()->comment('Contenu généré par IA');
            $table->boolean('is_ai_optimized')->default(false);
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index(['status', 'start_date']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_campaigns');
    }
};

