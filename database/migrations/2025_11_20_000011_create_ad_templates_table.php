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
        Schema::create('ad_templates', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->enum('campaign_type', ['BRAND_AWARENESS', 'LEAD_GENERATION', 'SALES', 'TRAFFIC', 'ENGAGEMENT']);
            $table->enum('content_type', ['TEXT', 'IMAGE', 'VIDEO', 'AUDIO', 'CAROUSEL']);
            $table->json('template_structure')->comment('Structure du template (champs, placeholders)');
            $table->json('example_content')->nullable()->comment('Exemple de contenu');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ad_templates');
    }
};

