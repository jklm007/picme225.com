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
        Schema::table('subscription_plans', function (Blueprint $table) {
            // Segmentation par catégorie de service (Taxi, Livraison, Voyage, etc.)
            $table->unsignedBigInteger('service_id')->nullable()->after('id');
            
            // Périodicité (DAILY, WEEKLY, MONTHLY) - Très important en CI
            $table->enum('period', ['DAILY', 'WEEKLY', 'MONTHLY'])->default('MONTHLY')->after('price');
            
            // Frais fixes additionnels (pour le modèle GOLD sans commission)
            $table->decimal('fixed_fee', 10, 2)->default(0)->after('commission_value');
            
            // Score de priorité IA (0-100)
            $table->integer('priority_weight')->default(0)->after('priority');
            
            // Badges & Gamification
            $table->string('badge_url')->nullable()->after('description');
            $table->boolean('show_on_marketplace')->default(false)->after('status');
            
            // Foreign Key
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropForeign(['service_id']);
            $table->dropColumn(['service_id', 'period', 'fixed_fee', 'priority_weight', 'badge_url', 'show_on_marketplace']);
        });
    }
};
